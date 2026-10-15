<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsBookingCalendar;
use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\OtpMail;
use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientInfo;
use App\Models\RescheduleRequest;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\RescheduleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use RuntimeException;

/**
 * Managing an appointment without an account.
 *
 *  - /appointment/manage/{token} — the "Manage my appointment" link in every
 *    email. The long random token IS the access: view the details, cancel,
 *    pick a new time, or answer a time the clinic proposed. Read-only once
 *    the appointment is no longer active.
 *  - /manage-booking — for someone who lost the email: verify the email
 *    with a code, then see every appointment booked under it, each with its
 *    manage link.
 *
 * Same rules as the Patient Portal: a patient's own reschedule goes back to
 * Pending for staff to approve; the clinic hears about every change.
 */
class ManageAppointmentController extends Controller
{
    use BuildsBookingCalendar, ManagesOtp;

    public function __construct(
        protected RescheduleService $reschedules,
        protected NotificationService $notifications,
        protected ActivityLogService $activityLog
    ) {
    }

    public function show(string $token)
    {
        $appointment = $this->byToken($token);

        return view('guest.manage', [
            'appointment' => $appointment,
            'isActive' => in_array($appointment->Status, ['Pending', 'Approved'], true),
        ]);
    }

    public function cancel(Request $request, string $token)
    {
        $appointment = $this->byToken($token);
        $data = $request->validate(['reason' => 'nullable|string|max:500']);

        if (!in_array($appointment->Status, ['Pending', 'Approved'], true)) {
            return redirect()->route('manage.show', $token)
                ->with('success', 'That appointment is no longer active — it may have already been ' . strtolower($appointment->Status) . '.');
        }

        $reason = trim((string) ($data['reason'] ?? ''));
        $this->releaseSlots($appointment);
        $appointment->Status = 'Cancelled';
        $appointment->DeclineReason = $reason !== '' ? "Cancelled by the patient: {$reason}" : 'Cancelled by the patient.';
        $appointment->save();

        $dateLabel = $appointment->AppointmentDate->format('F j, Y');
        $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
        $patientName = $appointment->patientInfo?->full_name ?: 'A patient';

        $this->notifications->notifyPatient(
            $appointment,
            'Appointment Cancelled',
            "Your appointment on {$dateLabel} at {$timeLabel} has been cancelled as requested.",
            'danger',
            'Cancelled'
        );
        $this->notifications->notifyAdmins(
            'Appointment Cancelled',
            "{$patientName}'s appointment on {$dateLabel} at {$timeLabel} was cancelled by the patient (no account)."
                . ($reason !== '' ? " Reason: {$reason}" : ''),
            'danger',
            $appointment->AppointmentID,
            'Cancelled'
        );
        $this->activityLog->log('Appointment Cancelled', "{$patientName}'s appointment on {$dateLabel} at {$timeLabel} was cancelled from the manage link.");

        return redirect()->route('manage.show', $token)->with('success', 'Your appointment has been cancelled. The time slot is open again.');
    }

    public function reschedulePage(Request $request, string $token)
    {
        $appointment = $this->byToken($token);
        if (!$appointment->can_reschedule) {
            return redirect()->route('manage.show', $token)->with('error', 'This appointment can no longer be rescheduled.');
        }

        if (!$request->has('dentist')) {
            $request->query->set('dentist', $appointment->DentistID);
        }
        if (!$request->has('bookMonth')) {
            $request->query->set('bookMonth', $appointment->AppointmentDate->format('Y-m'));
        }

        return view('guest.reschedule', array_merge($this->calendarData($request), [
            'appointment' => $appointment,
            'token' => $token,
            // Show the appointment being moved as "current" on the calendar.
            'bookCurrentPatientId' => $appointment->PatientID,
        ]));
    }

    public function reschedule(Request $request, string $token)
    {
        $appointment = $this->byToken($token);
        $data = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'dentist_id' => 'required|integer',
            'service_ids' => 'required|array|min:1|max:' . Service::MAX_PER_APPOINTMENT,
            'service_ids.*' => 'distinct|integer|exists:tbl_services,ServiceID',
        ]);

        try {
            $appointment = $this->reschedules->patientMove($appointment, (int) $data['dentist_id'], $data['date'], $data['time'], $data['service_ids']);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('booking_error', $e->getMessage());
        }

        return redirect()->route('manage.show', $token)
            ->with('success', "Your appointment moved to {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}. The clinic will review and approve it.");
    }

    public function acceptProposal(string $token)
    {
        $appointment = $this->byToken($token);
        $proposal = $this->proposal($appointment);

        try {
            $appointment = $this->reschedules->accept($proposal);
        } catch (RuntimeException $e) {
            return redirect()->route('manage.show', $token)->with('error', $e->getMessage());
        }

        return redirect()->route('manage.show', $token)
            ->with('success', "You're all set — your appointment is now on {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}.");
    }

    public function declineProposal(string $token)
    {
        $appointment = $this->byToken($token);
        $proposal = $this->proposal($appointment);

        try {
            $this->reschedules->decline($proposal);
        } catch (RuntimeException $e) {
            return redirect()->route('manage.show', $token)->with('error', $e->getMessage());
        }

        return redirect()->route('manage.show', $token)->with('success', 'Got it — you kept your original appointment time. The clinic has been told.');
    }

    // ---------------- "Manage my booking" lookup ----------------

    public function lookup()
    {
        $email = $this->lookupEmail();

        $appointments = $email
            ? Appointment::with(['patientInfo', 'dentist.staffInfo', 'service'])
                ->where(function ($q) use ($email) {
                    $q->whereRaw('LOWER(BookedByEmail) = ?', [$email])
                        ->orWhereIn('PatientID', PatientInfo::underEmail($email)->pluck('PatientID'));
                })
                ->orderByRaw("FIELD(Status, 'Pending', 'Approved') DESC")
                ->orderByDesc('AppointmentDate')
                ->limit(30)
                ->get()
            : collect();

        return view('guest.lookup', [
            'step' => $email ? 'list' : (session('manage_lookup_email') ? 'code' : 'email'),
            'pendingEmail' => session('manage_lookup_email'),
            'verifiedEmail' => $email,
            'appointments' => $appointments,
            'retryAfter' => $this->otpResendRetryAfter('manage_lookup'),
        ]);
    }

    public function lookupSend(Request $request)
    {
        $email = strtolower(trim((string) $request->validate(['email' => 'required|email|max:255'])['email']));

        if (UserAccount::whereRaw('LOWER(Email) = ?', [$email])->exists()) {
            return redirect()->route('login')
                ->withInput(['email' => $email])
                ->with('login_error', 'This email has an account — log in to see and manage your appointments in the Patient Portal.');
        }

        foreach (['manage-otp:' . $email => 5, 'manage-otp-ip:' . $request->ip() => 15] as $key => $max) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                return back()->withInput()->with('error', 'Too many code requests. Please try again in ' . $this->otpWaitLabel(RateLimiter::availableIn($key)) . '.');
            }
        }
        if ($request->has('resend') && $this->otpResendTooSoon('manage_lookup')) {
            return back()->with('error', 'Please wait ' . $this->otpWaitLabel($this->otpResendRetryAfter('manage_lookup')) . ' before requesting another code.');
        }
        RateLimiter::hit('manage-otp:' . $email, 900);
        RateLimiter::hit('manage-otp-ip:' . $request->ip(), 900);

        $code = $this->issueOtp('manage_lookup');
        session(['manage_lookup_email' => $email, 'manage_lookup_attempts' => 0]);

        try {
            Mail::to($email)->send(new OtpMail($code, 'confirm your email and see your appointments'));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', "We couldn't send a code to that email. Please check it and try again.");
        }

        return redirect()->route('manage.lookup')->with('success', "We sent a 6-digit code to {$email}.");
    }

    public function lookupVerify(Request $request)
    {
        $request->validate(['code' => 'required|string']);
        if (!session('manage_lookup_email')) {
            return redirect()->route('manage.lookup');
        }

        $attempts = (int) session('manage_lookup_attempts', 0) + 1;
        session(['manage_lookup_attempts' => $attempts]);
        if ($attempts > 5) {
            $this->clearOtp('manage_lookup');
            session()->forget('manage_lookup_email');

            return redirect()->route('manage.lookup')->with('error', 'Too many wrong codes. Please request a new one.');
        }
        if ($this->otpExpired('manage_lookup')) {
            return back()->with('error', 'That code has expired. Please request a new one.');
        }
        if (!$this->otpMatches('manage_lookup', $request->input('code'))) {
            return back()->with('error', 'That code is incorrect. Please check your email and try again.');
        }

        session(['manage_verified_email' => session('manage_lookup_email'), 'manage_verified_at' => now()->toIso8601String()]);
        $this->clearOtp('manage_lookup');
        session()->forget('manage_lookup_email');

        return redirect()->route('manage.lookup');
    }

    public function lookupReset()
    {
        $this->clearOtp('manage_lookup');
        session()->forget(['manage_lookup_email', 'manage_verified_email', 'manage_verified_at']);

        return redirect()->route('manage.lookup');
    }

    // ---------------- Helpers ----------------

    protected function byToken(string $token): Appointment
    {
        abort_unless(strlen($token) >= 32, 404);

        return Appointment::with(['patientInfo', 'service', 'services', 'dentist.staffInfo', 'pendingReschedule.dentist.staffInfo', 'attachments'])
            ->where('ManageToken', $token)
            ->firstOrFail();
    }

    protected function proposal(Appointment $appointment): RescheduleRequest
    {
        $proposal = $appointment->pendingReschedule;
        abort_unless($proposal, 404);

        return $proposal;
    }

    protected function lookupEmail(): ?string
    {
        $email = session('manage_verified_email');
        $at = session('manage_verified_at');

        if (!$email || !$at || Carbon::parse($at)->lt(now()->subMinutes(30))) {
            return null;
        }

        // Signed up since — their bookings live in the Patient Portal now.
        return UserAccount::whereRaw('LOWER(Email) = ?', [strtolower($email)])->exists() ? null : $email;
    }

    /** Free every slot the appointment held, unless another active appointment still holds it. */
    protected function releaseSlots(Appointment $appointment): void
    {
        $times = DentistSchedule::slotTimes();
        $start = array_search($appointment->AppointmentTime, $times, true);
        for ($offset = 0; $start !== false && $offset < $appointment->duration_slots && isset($times[$start + $offset]); $offset++) {
            $time = $times[$start + $offset];
            $stillHeld = Appointment::whereKeyNot($appointment->AppointmentID)
                ->where('DentistID', $appointment->DentistID)
                ->whereDate('AppointmentDate', $appointment->AppointmentDate)
                ->whereIn('Status', ['Pending', 'Approved'])->get()
                ->contains(function ($other) use ($times, $time) {
                    $otherStart = array_search($other->AppointmentTime, $times, true);

                    return $otherStart !== false && in_array($time, array_slice($times, $otherStart, $other->duration_slots), true);
                });
            if (!$stillHeld) {
                DentistSchedule::where('DentistID', $appointment->DentistID)
                    ->where('Date', $appointment->AppointmentDate->format('Y-m-d'))
                    ->where('Time', $time)
                    ->update(['Status' => 'Available']);
            }
        }
    }
}
