<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\OtpMail;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class StaffProfileController extends Controller
{
    use ManagesOtp;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    /**
     * "My Profile" — used by every admin session (true admin, staff, super
     * admin), not just staff. Route names stay staffProfile* for backward
     * compatibility: EnsureStaffIsVerified's allowlist references these
     * exact names to let an unverified staff account reach only this page.
     *
     * Access control is the 'admin' route middleware (routes/web.php).
     */
    protected function currentStaff(): UserAccount
    {
        return UserAccount::with('staffInfo')->findOrFail(session('user_id'));
    }

    public function edit(Request $request)
    {
        $staff = $this->currentStaff();
        $activeTab = $request->query('tab') === 'security' ? 'security' : 'profile';

        return view('staff.staff-userprofile', ['staff' => $staff, 'activeTab' => $activeTab]);
    }

    public function sendVerification(Request $request)
    {
        $staff = $this->currentStaff();

        if ($staff->EmailVerifiedAt) {
            return redirect()->route('staffProfile')->with('success', 'Your email is already verified.');
        }

        // Must wait 60 seconds between sends.
        if ($this->otpResendTooSoon('staff_verify')) {
            $wait = $this->otpWaitLabel($this->otpResendRetryAfter('staff_verify'));
            return redirect()->route('staffProfile')
                ->with('show_staff_verify', true)
                ->with('error', "Please wait {$wait} before requesting another code.");
        }

        $throttleKey = 'staff-verify:' . $staff->UserID;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('staffProfile')->with('error', "Too many requests. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = $this->issueOtp('staff_verify');
        session([
            'staff_verify_attempts' => 0,
            'show_staff_verify' => true,
        ]);

        Mail::to($staff->Email)->send(new OtpMail($code));

        return redirect()->route('staffProfile')->with('verify_sent', true);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        if (!session()->has('staff_verify_code')) {
            return redirect()->route('staffProfile')->with('error', 'Your verification code expired. Please request a new one.');
        }

        // The code is only good for 5 minutes.
        if ($this->otpExpired('staff_verify')) {
            $this->clearOtp('staff_verify');
            session()->forget('show_staff_verify');
            return redirect()->route('staffProfile')->with('error', 'Your verification code expired. Please request a new one.');
        }

        if (!$this->otpMatches('staff_verify', $request->code)) {
            $attempts = session('staff_verify_attempts', 0) + 1;

            if ($attempts >= 5) {
                $this->clearOtp('staff_verify');
                session()->forget('show_staff_verify');
                return redirect()->route('staffProfile')->with('error', 'Too many incorrect attempts. Please request a new code.');
            }

            session(['staff_verify_attempts' => $attempts]);

            return redirect()->route('staffProfile')
                ->with('show_staff_verify', true)
                ->with('error', 'Incorrect code. Please try again.');
        }

        $staff = $this->currentStaff();
        $staff->EmailVerifiedAt = now();
        $staff->save();

        $this->clearOtp('staff_verify');
        session()->forget('show_staff_verify');

        return redirect()->route('staffProfile')->with('success', 'Email verified! You can now change your password.');
    }

    public function updatePassword(Request $request)
    {
        // The super admin authenticates against .env credentials, not a
        // stored hash — there's no real password here to change. The
        // Security tab is hidden for this session already; this is just
        // the defensive backend check.
        if (session('is_super_admin')) {
            return redirect()->route('staffProfile')->with('error', 'Your login is managed by server configuration, not a stored password.');
        }

        $staff = $this->currentStaff();

        if (!$staff->EmailVerifiedAt) {
            return redirect()->route('staffProfile')->with('error', 'Please verify your email before changing your password.');
        }

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!Hash::check($data['current_password'], $staff->Password)) {
            $this->activityLog->log('Failed Password Change', 'Entered the wrong current password when trying to change it (My Profile → Security).', $staff->UserID);
            return redirect()->route('staffProfile', ['tab' => 'security'])
                ->withInput($request->only('current_password', 'password', 'password_confirmation'))
                ->with('error', 'Your current password is incorrect.');
        }

        $staff->Password = Hash::make($data['password']);
        $staff->save();

        $this->activityLog->log('Password Changed', 'Changed own account password (My Profile → Security).', $staff->UserID);

        return redirect()->route('staffProfile')->with('success', 'Password updated successfully.');
    }
}
