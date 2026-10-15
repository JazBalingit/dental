<?php
// Place in: app/Http/Controllers/RegisterController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\OtpMail;
use App\Models\PatientInfo;
use App\Models\SystemSetting;
use App\Models\UserAccount;
use App\Services\BookingService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    use ManagesOtp;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function create()
    {
        return view('login_signup.signup', [
            'privacyPolicy' => SystemSetting::get('privacy_policy_content', ''),
            'legalTerms' => SystemSetting::get('legal_terms_content', ''),
            'otpRetrySeconds' => $this->otpResendRetryAfter('otp'),
        ]);
    }

    /**
     * Step 1: validate the signup form. If everything (including
     * password === confirm password) checks out, DO NOT save to the
     * database yet — stash it in the session, email a 6-digit code,
     * and show the OTP modal.
     */
    public function store(Request $request)
    {
        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            // Accounts are for adults — a parent books for their child from
            // their own account ("Book for Someone Else").
            'birthdate' => 'required|date|before_or_equal:' . BookingService::adultCutoff(),
            'gender' => 'required|string',
            'religion' => ['nullable', 'string', 'max:100', $nameRule],
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'occupation' => ['nullable', 'string', 'max:150', $nameRule],
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_useraccount,Email',
            'phone' => 'required|digits:11',
            // 'confirmed' automatically checks password === password_confirmation
            'password' => ['required', 'confirmed', Password::defaults()],
            'agree_terms' => 'accepted',
        ], [
            'birthdate.before_or_equal' => 'You must be 18 or older to create an account. A parent or guardian can book for you from their account using "Book for Someone Else".',
            'phone.digits' => 'Mobile number must be exactly 11 digits.',
            'agree_terms.accepted' => 'You must agree to the Privacy Policy and Terms to create an account.',
            'last_name.regex' => 'Last name may only contain letters.',
            'first_name.regex' => 'First name may only contain letters.',
            'middle_name.regex' => 'Middle name may only contain letters.',
            'religion.regex' => 'Religion may only contain letters.',
            'nationality.regex' => 'Nationality may only contain letters.',
            'occupation.regex' => 'Occupation may only contain letters.',
        ]);

        $throttleKey = 'signup-otp:' . strtolower($data['email']);
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('signup')->with('otp_error', "Too many attempts. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        // Age is computed from the birthdate — no manual input needed.
        $age = \Carbon\Carbon::parse($data['birthdate'])->age;

        $pending = [
            'Email' => $data['email'],
            'Password' => Hash::make($data['password']),
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => $age,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Address' => $data['address'],
            'Occupation' => $data['occupation'] ?? null,
        ];

        $code = $this->issueOtp('otp');

        session([
            'pending_registration' => $pending,
            'otp_email' => $data['email'],
            'otp_attempts' => 0,
            'show_otp' => true,
        ]);
        session()->forget('otp_error');

        Mail::to($data['email'])->send(new OtpMail($code));

        return redirect()->route('signup')->with('otp_sent', true);
    }

    /**
     * Step 2: check the code the patient typed against the one in session.
     * Only on a correct match do we actually write to tbl_userAcc / tbl_patientInfo.
     */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        if (!session()->has('pending_registration') || !session()->has('otp_code')) {
            return redirect()->route('signup')->with('otp_expired', true);
        }

        // The code is only good for 5 minutes — past that, make them resend.
        if ($this->otpExpired('otp')) {
            session()->forget(['pending_registration', 'otp_email', 'otp_attempts', 'show_otp']);
            $this->clearOtp('otp');
            return redirect()->route('signup')->with('otp_expired', true);
        }

        if (!$this->otpMatches('otp', $request->code)) {
            $attempts = session('otp_attempts', 0) + 1;

            if ($attempts >= 5) {
                session()->forget(['pending_registration', 'otp_email', 'otp_attempts', 'show_otp']);
                $this->clearOtp('otp');
                return redirect()->route('signup')->with('otp_expired', true);
            }

            session(['otp_attempts' => $attempts]);

            return redirect()->route('signup')
                ->withInput($request->only('code'))
                ->with('show_otp', true)
                ->with('otp_error', 'Incorrect code. Please try again.');
        }

        $pending = session('pending_registration');

        $user = UserAccount::create([
            'Email' => $pending['Email'],
            'Password' => $pending['Password'],
            'AccountRole' => 'patient',
            'DateCreated' => now(),
        ]);

        $details = collect($pending)->except(['Email', 'Password'])->toArray();
        $linked = $this->linkExistingRecords($user, $details);

        $this->activityLog->log('Create', "Registered a new patient account ({$user->Email})."
            . ($linked ? " Linked {$linked} existing record(s) booked with this email." : ''), $user->UserID);

        session()->forget(['pending_registration', 'otp_email', 'otp_attempts', 'show_otp']);
        $this->clearOtp('otp');

        return redirect()->route('login')->with('registered', true);
    }

    /**
     * "Didn't receive the code? Resend" — generates a fresh code,
     * re-sends the email, keeps the modal open.
     */
    public function resend(Request $request)
    {
        if (!session()->has('otp_email')) {
            return redirect()->route('signup')->with('otp_expired', true);
        }

        // Must wait 60 seconds between sends.
        if ($this->otpResendTooSoon('otp')) {
            $wait = $this->otpWaitLabel($this->otpResendRetryAfter('otp'));
            return redirect()->route('signup')
                ->with('show_otp', true)
                ->with('otp_error', "Please wait {$wait} before requesting another code.");
        }

        $throttleKey = 'signup-otp:' . strtolower(session('otp_email'));
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('signup')
                ->with('show_otp', true)
                ->with('otp_error', "Too many attempts. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = $this->issueOtp('otp');

        session(['otp_attempts' => 0, 'show_otp' => true]);
        session()->forget('otp_error');

        Mail::to(session('otp_email'))->send(new OtpMail($code));

        return redirect()->route('signup')->with('otp_resent', true);
    }

    /**
     * Closing the OTP modal (the X button). Because `show_otp` is a
     * regular session value (not a one-time flash), simply reloading
     * the page would reopen it forever — this explicitly clears it.
     */
    public function cancelOtp(Request $request)
    {
        session()->forget(['pending_registration', 'otp_email', 'otp_attempts', 'show_otp']);
        $this->clearOtp('otp');

        return redirect()->route('signup');
    }
    /**
     * The email was just verified, so everything booked with it becomes
     * this account's:
     *
     *  - the patient record that IS this person (a guest booking or walk-in
     *    with this email and the same name or birthdate) gets the account
     *    attached and their signup details — no duplicate patient, and their
     *    past visits, records and odontogram are in the portal right away;
     *  - people booked under this email (their children…) get this account as
     *    their guardian, so they show up under "Book for Someone Else";
     *  - appointments booked with this email are marked as booked by them.
     *
     * Returns how many existing records were linked.
     */

    protected function linkExistingRecords(UserAccount $user, array $details): int
    {
        $email = strtolower($user->Email);
        $candidates = PatientInfo::underEmail($email)->get();

        $self = $candidates
            ->filter(fn ($p) => !$p->isDependentOf($email))
            ->sortByDesc(fn ($p) => (int) (
                ($p->DateOfBirth?->format('Y-m-d') === $details['DateOfBirth'])
                + (strcasecmp($p->FirstName, $details['FirstName']) === 0 && strcasecmp($p->LastName, $details['LastName']) === 0)
            ))
            ->first(fn ($p) => $p->DateOfBirth?->format('Y-m-d') === $details['DateOfBirth']
                || (strcasecmp($p->FirstName, $details['FirstName']) === 0 && strcasecmp($p->LastName, $details['LastName']) === 0));

        if ($self) {
            $self->fill($details + ['UserID' => $user->UserID, 'Email' => $user->Email, 'IsWalkIn' => $self->IsWalkIn]);
            $self->save();
        } else {
            $self = PatientInfo::create($details + ['UserID' => $user->UserID]);
        }

        $dependents = $candidates->filter(fn ($p) => $p->PatientID !== $self->PatientID && $p->isDependentOf($email));
        foreach ($dependents as $dependent) {
            $dependent->GuardianUserID = $user->UserID;
            $dependent->GuardianEmail = $dependent->GuardianEmail ?: $user->Email;
            $dependent->save();
        }

        \App\Models\Appointment::whereNull('BookedByUserID')
            ->whereRaw('LOWER(BookedByEmail) = ?', [$email])
            ->update(['BookedByUserID' => $user->UserID]);

        return ($self->wasRecentlyCreated ? 0 : 1) + $dependents->count();
    }
}
