<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\OtpMail;
use App\Models\Appointment;
use App\Models\AppointmentAttachment;
use App\Models\MedicalHistory;
use App\Models\PatientInfo;
use App\Models\Service;
use App\Models\SystemSetting;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use App\Services\BookingService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Booking from the public landing page without an account.
 *
 *   1. The landing-page calendar (same picker as the Patient Portal) posts
 *      the chosen dentist / date / time / services / note / photos to
 *      start(); they're kept in the session.
 *   2. /book asks for an email and sends a 6-digit code to it.
 *   3. Once the code checks out, /book asks who the appointment is for:
 *      the people already booked under that email, yourself (the same
 *      details as signup — 18+ only), or someone else (e.g. your child —
 *      you're saved as their guardian).
 *   4. confirm() creates the Pending request; staff approve it as usual and
 *      every update is emailed with a "Manage my appointment" link.
 *
 * An email that already belongs to an account is sent to log in instead,
 * so a patient's bookings never get split between a portal and a guest.
 */
class GuestBookingController extends Controller
{
    use ManagesOtp;

    /** How long a verified email stays verified for booking. */
    protected const VERIFIED_MINUTES = 30;

    /** Most upcoming requests one email may have open at once (across everyone it books for). */
    protected const MAX_OPEN_PER_EMAIL = 3;

    /** Most people (yourself included) one email may keep on file. */
    public const MAX_PEOPLE_PER_EMAIL = 3;

    public function __construct(
        protected BookingService $booking,
        protected NotificationService $notifications,
        protected ActivityLogService $activityLog
    ) {
    }

    /** Step 1 — the calendar's confirm form lands here. */
    public function start(Request $request)
    {
        if (session('user_id')) {
            // Signed in already: patients book from the portal, staff use Follow-up.
            return in_array(session('user_role'), UserAccount::ADMIN_ROLES, true)
                ? redirect()->route('followUp')->with('error', 'You are signed in as clinic staff — book patients from Follow-up Appointment.')
                : redirect()->route('userAppointment.book');
        }

        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'dentist_id' => 'required|integer',
            'service_ids' => 'required|array|min:1|max:' . Service::MAX_PER_APPOINTMENT,
            'service_ids.*' => 'distinct|integer|exists:tbl_services,ServiceID',
            'attachments' => 'nullable|array|max:' . AppointmentAttachment::MAX_FILES,
            'attachments.*' => 'image|mimes:jpg,jpeg,png,webp|max:' . AppointmentAttachment::MAX_KB,
            'patient_notes' => 'nullable|string|max:1000|required_without:attachments',
        ], [
            'patient_notes.required_without' => 'Please add a photo or write a note for your dentist so they know what you need.',
            'service_ids.max' => 'You can choose up to ' . Service::MAX_PER_APPOINTMENT . ' services per appointment.',
            'attachments.max' => 'You can attach up to ' . AppointmentAttachment::MAX_FILES . ' photos.',
            'attachments.*.image' => 'Attachments must be photos (JPG, PNG or WebP).',
            'attachments.*.mimes' => 'Attachments must be photos (JPG, PNG or WebP).',
            'attachments.*.max' => 'Each photo must be under 1 MB. Please try a smaller photo.',
        ]);

        try {
            $this->booking->dentist((int) $data['dentist_id']);
            $this->booking->assertBookable($data['date'], $data['time']);
        } catch (RuntimeException $e) {
            return redirect()->to(route('landingPage') . '#appointment')->with('booking_error', $e->getMessage());
        }

        $this->discardPhotos();

        // Every new booking starts at the email step and needs a fresh code —
        // nothing from an earlier (or unfinished) booking carries over.
        $this->clearOtp('guest_book');
        session()->forget(['guest_book_email', 'guest_verified_email', 'guest_verified_at']);

        // Photos wait on disk (not in the session) until the booking is confirmed.
        $dir = $this->uploadDir();
        File::ensureDirectoryExists($dir);
        $this->sweepOldUploads($dir);
        $photos = [];
        foreach ($request->file('attachments', []) as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . Str::random(40);
            File::copy($file->getRealPath(), $path);
            $photos[] = [
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ];
        }

        session(['guest_booking' => [
            'date' => $data['date'],
            'time' => $data['time'],
            'dentist_id' => (int) $data['dentist_id'],
            'service_ids' => array_map('intval', $data['service_ids']),
            'patient_notes' => trim((string) ($data['patient_notes'] ?? '')),
            'photos' => $photos,
        ]]);

        return redirect()->route('guestBooking.show');
    }

    /** The email → code → patient details page. */
    public function show()
    {
        if (session('user_id')) {
            return in_array(session('user_role'), UserAccount::ADMIN_ROLES, true)
                ? redirect()->route('followUp')
                : redirect()->route('userAppointment.book');
        }

        $slot = session('guest_booking');
        if (!$slot) {
            return redirect()->to(route('landingPage') . '#appointment')
                ->with('booking_error', 'Please choose a date and time first.');
        }

        $verified = $this->verifiedEmail();
        $people = $verified ? PatientInfo::underEmail($verified)->with('latestMedicalHistory')->orderBy('FirstName')->get() : collect();
        $selfRecord = $verified ? $people->first(fn ($p) => !$p->isDependentOf($verified)) : null;

        return view('guest.book', [
            'step' => $this->currentStep(),
            'summary' => $this->summary($slot),
            'pendingEmail' => session('guest_book_email'),
            'verifiedEmail' => $verified,
            'people' => $people,
            'selfRecord' => $selfRecord,
            'maxPeople' => self::MAX_PEOPLE_PER_EMAIL,
            'retryAfter' => $this->otpResendRetryAfter('guest_book'),
            'adultCutoff' => BookingService::adultCutoff(),
            'privacyPolicy' => SystemSetting::get('privacy_policy_content', ''),
            'legalTerms' => SystemSetting::get('legal_terms_content', ''),
        ]);
    }

    /** Step 2 — send a code to the email. */
    public function sendCode(Request $request)
    {
        $email = strtolower(trim((string) $request->validate(['email' => 'required|email|max:255'])['email']));

        // Came back from "Patient details" and kept the same, still-verified
        // email — carry on without a new code.
        if (($verified = $this->verifiedEmail()) && strcasecmp($verified, $email) === 0) {
            session()->forget('guest_book_editing_email');

            return redirect()->route('guestBooking.show');
        }
        session()->forget(['guest_book_editing_email', 'guest_verified_email', 'guest_verified_at']);

        if (UserAccount::whereRaw('LOWER(Email) = ?', [$email])->exists()) {
            return redirect()->route('login')
                ->withInput(['email' => $email])
                ->with('login_error', 'This email already has an account. Log in to book — your appointment will show up in your Patient Portal.');
        }

        if ($error = $this->throttle($request, $email)) {
            return back()->withInput()->with('booking_error', $error);
        }

        $code = $this->issueOtp('guest_book');
        session(['guest_book_email' => $email, 'guest_book_attempts' => 0]);

        try {
            Mail::to($email)->send(new OtpMail($code, 'confirm your email and finish booking your appointment'));
        } catch (\Throwable $e) {
            report($e);
            $this->clearOtp('guest_book');
            session()->forget('guest_book_email');

            return back()->withInput()->with('booking_error', "We couldn't send a code to that email. Please check it and try again.");
        }

        return redirect()->route('guestBooking.show')->with('success', "We sent a 6-digit code to {$email}.");
    }

    public function resend(Request $request)
    {
        $email = session('guest_book_email');
        if (!$email) {
            return redirect()->route('guestBooking.show');
        }
        if ($this->otpResendTooSoon('guest_book')) {
            return back()->with('booking_error', 'Please wait ' . $this->otpWaitLabel($this->otpResendRetryAfter('guest_book')) . ' before requesting another code.');
        }
        if ($error = $this->throttle($request, $email)) {
            return back()->with('booking_error', $error);
        }

        $code = $this->issueOtp('guest_book');
        session(['guest_book_attempts' => 0]);

        try {
            Mail::to($email)->send(new OtpMail($code, 'confirm your email and finish booking your appointment'));
        } catch (\Throwable $e) {
            report($e);

            return back()->with('booking_error', "We couldn't send the code. Please try again in a moment.");
        }

        return back()->with('success', "A new code is on its way to {$email}.");
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        if (!session('guest_book_email')) {
            return redirect()->route('guestBooking.show');
        }

        $attempts = (int) session('guest_book_attempts', 0) + 1;
        session(['guest_book_attempts' => $attempts]);

        if ($attempts > 5) {
            $this->clearOtp('guest_book');
            session()->forget('guest_book_email');

            return redirect()->route('guestBooking.show')->with('booking_error', 'Too many wrong codes. Please request a new one.');
        }

        if ($this->otpExpired('guest_book')) {
            return back()->with('booking_error', 'That code has expired. Please request a new one.');
        }

        if (!$this->otpMatches('guest_book', $request->input('code'))) {
            return back()->with('booking_error', 'That code is incorrect. Please check your email and try again.');
        }

        session([
            'guest_verified_email' => session('guest_book_email'),
            'guest_verified_at' => now()->toIso8601String(),
        ]);
        $this->clearOtp('guest_book');
        session()->forget('guest_book_email');

        return redirect()->route('guestBooking.show')->with('success', 'Email verified.');
    }

    public function changeEmail()
    {
        $this->clearOtp('guest_book');
        session()->forget(['guest_book_email', 'guest_verified_email', 'guest_verified_at', 'guest_book_editing_email']);

        return redirect()->route('guestBooking.show');
    }

    /**
     * "Back" — one step at a time: Patient details → Your email (still
     * verified, so keeping the same email skips the code), Verify code →
     * Your email, Your email → the calendar.
     */
    public function back()
    {
        switch ($this->currentStep()) {
            case 'details':
                session(['guest_book_editing_email' => true]);
                break;
            case 'code':
                $email = session('guest_book_email');
                $this->clearOtp('guest_book');
                session()->forget('guest_book_email');

                return redirect()->route('guestBooking.show')->withInput(['email' => $email]);
            default:
                return $this->editSlot();
        }

        return redirect()->route('guestBooking.show');
    }

    /**
     * Back to the calendar with the day's time/services picker reopened and
     * filled in (landing page reads ?resume=1 — see UserController). The
     * slot stays in the session until a new one is submitted; photos have
     * to be picked again since a file input can't be pre-filled.
     */
    public function editSlot()
    {
        $slot = session('guest_booking');
        if (!$slot) {
            return redirect()->to(route('landingPage') . '#appointment');
        }

        return redirect()->to(route('landingPage', [
            'bookMonth' => Carbon::parse($slot['date'])->format('Y-m'),
            'dentist' => $slot['dentist_id'],
            'resume' => 1,
        ]) . '#appointment');
    }

    /**
     * Take someone this email booked for off its list (the "×" on their
     * card). Their records stay with the clinic — they just stop showing up
     * under this email. Not the email's own record, and not while they have
     * an upcoming appointment.
     */
    public function removePerson(int $id)
    {
        $email = $this->verifiedEmail();
        if (!$email) {
            return redirect()->route('guestBooking.show')->with('booking_error', 'Please verify your email again — it has been a while.');
        }

        $person = PatientInfo::underEmail($email)->find($id);
        if (!$person || !$person->isDependentOf($email)) {
            return redirect()->route('guestBooking.show')->with('booking_error', 'That person is not on your list.');
        }

        if (Appointment::upcoming()->where('PatientID', $person->PatientID)->exists()) {
            return redirect()->route('guestBooking.show')->with('booking_error', "{$person->full_name} has an upcoming appointment. Cancel it first if you want to remove them.");
        }

        foreach (['GuardianEmail', 'ParentsEmail'] as $column) {
            if (strcasecmp((string) $person->{$column}, $email) === 0) {
                $person->{$column} = null;
            }
        }
        $person->save();

        $this->activityLog->log('Edit', "Guest {$email} removed {$person->full_name} from the people they book for.");

        return redirect()->route('guestBooking.show')->with('success', "{$person->full_name} was removed from your list.");
    }

    /** Leave the guest flow and go back to the calendar. */
    public function cancel()
    {
        $this->discardPhotos();
        $this->clearOtp('guest_book');
        session()->forget(['guest_booking', 'guest_book_email', 'guest_verified_email', 'guest_verified_at']);

        return redirect()->to(route('landingPage') . '#appointment');
    }

    /** Step 4 — create the Pending request. */
    public function confirm(Request $request)
    {
        $slot = session('guest_booking');
        $email = $this->verifiedEmail();

        if (!$slot) {
            return redirect()->to(route('landingPage') . '#appointment')->with('booking_error', 'Please choose a date and time first.');
        }
        if (!$email) {
            return redirect()->route('guestBooking.show')->with('booking_error', 'Please verify your email again — it has been a while.');
        }

        $people = PatientInfo::underEmail($email)->get();
        $selfRecord = $people->first(fn ($p) => !$p->isDependentOf($email));
        $choice = (string) $request->input('patient_choice');

        $rules = ['patient_choice' => 'required|string', 'agree_terms' => 'accepted'];
        $existing = ctype_digit($choice) ? $people->firstWhere('PatientID', (int) $choice) : null;

        if (in_array($choice, ['self', 'other'], true) && $people->count() >= self::MAX_PEOPLE_PER_EMAIL) {
            return back()->withInput()->with('booking_error', 'One email can keep up to ' . self::MAX_PEOPLE_PER_EMAIL . ' people. Remove someone from your list to add a new person.');
        }

        if ($choice === 'self') {
            $rules += BookingService::selfRules();
        } elseif ($choice === 'other') {
            $rules += BookingService::otherRules();
        } elseif (!$existing) {
            return back()->withInput()->with('booking_error', 'Please choose who this appointment is for.');
        }

        // Who's booking: the email's own patient record when there is one,
        // otherwise they tell us their name and number.
        $needsBooker = !$selfRecord && $choice !== 'self' && !($existing && !$existing->isDependentOf($email));
        if ($needsBooker && !($existing && $existing->ParentsName && $existing->ParentsContactNumber)) {
            $nameRule = "regex:/^[\pL\s'.-]+$/u";
            $rules['booker_name'] = ['required', 'string', 'max:150', $nameRule];
            $rules['booker_phone'] = 'required|digits:11';
        }

        // Medical history: a returning person may answer "still the same".
        MedicalHistory::takeInput($request, match (true) {
            $choice === 'self' => 'mh_self',
            $choice === 'other' => 'mh_other',
            default => 'mh_p' . (int) $choice,
        });
        $latestMedical = $existing?->latestMedicalHistory;
        $rules += MedicalHistory::bookingRules($request->input('mh_status'), $latestMedical);

        $data = $request->validate($rules, BookingService::messages() + MedicalHistory::messages() + [
            'patient_choice.required' => 'Please choose who this appointment is for.',
            'agree_terms.accepted' => 'Please agree to the Privacy Policy and Terms to request an appointment.',
            'booker_name.required' => 'Please enter your name.',
            'booker_phone.required' => 'Please enter your mobile number.',
        ]);

        if ($choice === 'self' && $selfRecord) {
            return back()->withInput()->with('booking_error', 'You already have a patient record with this email — please choose your name from the list.');
        }

        $booker = [
            'user_id' => null,
            'email' => $email,
            'for_other' => $choice === 'other' || ($existing && $existing->isDependentOf($email)),
        ];
        if ($choice === 'self') {
            $booker['name'] = trim($data['first_name'] . ' ' . $data['last_name']);
            $booker['phone'] = $data['phone'];
        } elseif ($selfRecord) {
            $booker['name'] = $selfRecord->full_name;
            $booker['phone'] = $selfRecord->PhoneNumber;
        } elseif ($existing && !$existing->isDependentOf($email)) {
            $booker['name'] = $existing->full_name;
            $booker['phone'] = $existing->PhoneNumber;
        } else {
            $booker['name'] = $data['booker_name'] ?? $existing?->ParentsName;
            $booker['phone'] = $data['booker_phone'] ?? $existing?->ParentsContactNumber;
        }

        $openForEmail = Appointment::upcoming()->whereRaw('LOWER(BookedByEmail) = ?', [$email])->count();
        if ($openForEmail >= self::MAX_OPEN_PER_EMAIL) {
            return back()->withInput()->with('booking_error', 'This email already has ' . self::MAX_OPEN_PER_EMAIL . ' upcoming appointments. Please wait until one is completed, or cancel one, before booking another.');
        }

        $createPatient = match ($choice) {
            'self' => fn () => PatientInfo::create(BookingService::selfAttributes($data, $email) + ['IsWalkIn' => false, 'UserID' => null]),
            'other' => fn () => PatientInfo::create(BookingService::otherAttributes($data, $booker, $selfRecord?->Address)),
            default => null,
        };

        try {
            $dentist = $this->booking->dentist($slot['dentist_id']);
            $appointment = $this->booking->create(
                $existing,
                $dentist,
                $slot,
                $this->loadPhotos($slot['photos'] ?? []),
                $booker,
                $createPatient,
                fn ($appointment) => $appointment->update(['MedicalHistoryID' => MedicalHistory::forBooking($data, $latestMedical, $appointment->PatientID, 'patient', null, $booker['name'])->MedicalHistoryID])
            );
        } catch (\Throwable $e) {
            if (!$e instanceof RuntimeException) {
                report($e);
            }
            $message = $e instanceof RuntimeException ? $e->getMessage() : 'Could not book this appointment. Please try again.';
            $this->activityLog->log('Failed Booking', "Guest booking by {$email} failed: {$message}");

            return back()->withInput()->with('booking_error', $message);
        }

        // Done — the next booking verifies the email again (a shared
        // computer mustn't keep booking under someone else's email).
        $this->discardPhotos();
        session()->forget(['guest_booking', 'guest_verified_email', 'guest_verified_at']);

        $appointment->load('patientInfo');
        $patientName = $appointment->patientInfo->full_name;
        $when = $appointment->AppointmentDate->format('F j, Y') . ' at ' . Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');

        $this->notifications->notifyPatient(
            $appointment,
            'Appointment Booked',
            $booker['for_other']
                ? "Your appointment request for {$patientName} has been received and is now pending approval. We'll email you as soon as the clinic reviews it."
                : "Your appointment request has been received and is now pending approval. We'll email you as soon as the clinic reviews it.",
            'warning',
            'Pending'
        );

        $this->notifications->notifyAdmins(
            'New Appointment (Guest)',
            $booker['for_other']
                ? "{$booker['name']} (no account) has requested an appointment with {$dentist->display_name} for {$patientName}" . ($appointment->patientInfo->Relationship ? " ({$appointment->patientInfo->Relationship})" : '') . '.'
                : "{$patientName} (no account) has requested an appointment with {$dentist->display_name}.",
            'info',
            $appointment->AppointmentID,
            'Pending'
        );

        $this->activityLog->log('Appointment Booked', "Guest booking by {$email}: {$patientName} with {$dentist->display_name} on {$when} — {$appointment->TypeOfAppointment}.");

        return redirect()->route('manage.show', $appointment->ensureManageToken())
            ->with('success', "Request sent! We emailed the details to {$email}.")
            ->with('just_booked', true);
    }

    // ------------------------------------------------------------------

    protected function currentStep(): string
    {
        if ($this->verifiedEmail()) {
            return session('guest_book_editing_email') ? 'email' : 'details';
        }

        return session('guest_book_email') ? 'code' : 'email';
    }

    protected function verifiedEmail(): ?string
    {
        $email = session('guest_verified_email');
        $at = session('guest_verified_at');

        if (!$email || !$at || Carbon::parse($at)->lt(now()->subMinutes(self::VERIFIED_MINUTES))) {
            return null;
        }

        // Someone signed up with this email since it was verified — from
        // now on it books from the Patient Portal, never as a guest.
        if (UserAccount::whereRaw('LOWER(Email) = ?', [strtolower($email)])->exists()) {
            session()->forget(['guest_verified_email', 'guest_verified_at']);

            return null;
        }

        return $email;
    }

    /** Code requests: 5 per email and 15 per IP address every 15 minutes. */
    protected function throttle(Request $request, string $email): ?string
    {
        foreach (['guest-otp:' . $email => 5, 'guest-otp-ip:' . $request->ip() => 15] as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return 'Too many code requests. Please try again in ' . $this->otpWaitLabel(RateLimiter::availableIn($key)) . '.';
            }
        }
        RateLimiter::hit('guest-otp:' . $email, 900);
        RateLimiter::hit('guest-otp-ip:' . $request->ip(), 900);

        return null;
    }

    protected function summary(array $slot): array
    {
        $services = Service::whereIn('ServiceID', $slot['service_ids'])->get();
        $slots = max(1, (int) ceil($services->sum(fn ($s) => $s->DurationMinutes ?? 60) / \App\Models\DentistSchedule::SLOT_MINUTES));

        return [
            'dentist' => UserAccount::find($slot['dentist_id'])?->display_name ?? '—',
            'date' => Carbon::parse($slot['date'])->format('l, F j, Y'),
            'time' => Carbon::createFromFormat('H:i', $slot['time'])->format('g:i A'),
            'services' => $services->pluck('ServiceName')->implode(', '),
            'duration' => \App\Models\DentistSchedule::formatSlotDuration($slots),
            'photos' => count($slot['photos'] ?? []),
            'notes' => $slot['patient_notes'] ?? '',
        ];
    }

    protected function uploadDir(): string
    {
        return storage_path('app' . DIRECTORY_SEPARATOR . 'guest-uploads');
    }

    protected function loadPhotos(array $photos): array
    {
        return collect($photos)
            ->filter(fn ($p) => is_file($p['path']))
            ->map(fn ($p) => [
                'name' => $p['name'],
                'mime' => $p['mime'],
                'size' => $p['size'],
                'data' => file_get_contents($p['path']),
            ])->values()->all();
    }

    protected function discardPhotos(): void
    {
        foreach (session('guest_booking.photos', []) as $photo) {
            if (is_file($photo['path']) && str_starts_with(realpath($photo['path']), realpath($this->uploadDir()))) {
                @unlink($photo['path']);
            }
        }
    }

    /** Photos from bookings that were never finished — older than a day. */
    protected function sweepOldUploads(string $dir): void
    {
        foreach (File::files($dir) as $file) {
            if ($file->getMTime() < now()->subDay()->getTimestamp()) {
                @unlink($file->getPathname());
            }
        }
    }
}
