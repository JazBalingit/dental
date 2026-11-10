<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\OtpMail;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * The screen a patient account created by an admin (User Accounts → Add
 * User) is held on until its email is proven — the patient-side twin of the
 * staff verification on StaffProfileController. EnsurePatientIsVerified sends
 * every other page here while EmailVerifiedAt is null.
 */
class PatientVerificationController extends Controller
{
    use ManagesOtp;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    protected function currentPatient(): UserAccount
    {
        return UserAccount::findOrFail(session('user_id'));
    }

    public function show()
    {
        $patient = $this->currentPatient();

        if ($patient->EmailVerifiedAt) {
            return redirect()->route('userAppointment');
        }

        return view('login_signup.verify-email', [
            'email' => $patient->Email,
            'codeSent' => session()->has('patient_verify_code'),
            'otpRetrySeconds' => $this->otpResendRetryAfter('patient_verify'),
        ]);
    }

    public function send(Request $request)
    {
        $patient = $this->currentPatient();

        if ($patient->EmailVerifiedAt) {
            return redirect()->route('userAppointment');
        }

        // Must wait 60 seconds between sends.
        if ($this->otpResendTooSoon('patient_verify')) {
            $wait = $this->otpWaitLabel($this->otpResendRetryAfter('patient_verify'));
            return redirect()->route('patientVerify')->with('error', "Please wait {$wait} before requesting another code.");
        }

        $throttleKey = 'patient-verify:' . $patient->UserID;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('patientVerify')->with('error', "Too many requests. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = $this->issueOtp('patient_verify');
        session(['patient_verify_attempts' => 0]);

        Mail::to($patient->Email)->send(new OtpMail($code, 'verify your email address and start using your account'));

        return redirect()->route('patientVerify')->with('success', 'Code sent! Check your inbox (and spam folder).');
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        if (!session()->has('patient_verify_code')) {
            return redirect()->route('patientVerify')->with('error', 'Your verification code expired. Please request a new one.');
        }

        // The code is only good for 5 minutes.
        if ($this->otpExpired('patient_verify')) {
            $this->clearOtp('patient_verify');
            return redirect()->route('patientVerify')->with('error', 'Your verification code expired. Please request a new one.');
        }

        if (!$this->otpMatches('patient_verify', $request->code)) {
            $attempts = session('patient_verify_attempts', 0) + 1;

            if ($attempts >= 5) {
                $this->clearOtp('patient_verify');
                return redirect()->route('patientVerify')->with('error', 'Too many incorrect attempts. Please request a new code.');
            }

            session(['patient_verify_attempts' => $attempts]);

            return redirect()->route('patientVerify')
                ->withInput($request->only('code'))
                ->with('verify_error', 'Incorrect code. Please try again.');
        }

        $patient = $this->currentPatient();
        $patient->EmailVerifiedAt = now();
        $patient->save();

        $this->clearOtp('patient_verify');
        session()->forget('patient_verify_attempts');

        $this->activityLog->log('Edit', 'Verified own email address.', $patient->UserID);

        return redirect()->route('userAppointment')->with('success', 'Email verified! Your account is ready to use.');
    }
}
