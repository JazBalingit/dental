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
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

/**
 * The super admin account lifecycle — both directions.
 *
 * SET UP (activate): a session that logged in with the config/superadmin.php
 * (.env) credentials carries session('super_admin_setup') and is locked to the
 * setup screen by EnsureSuperAdminClaimed. It picks an email (must not already
 * be in use) and a password, then confirms the email with a 6-digit code sent
 * to that address. Only once the code checks out is the provisioned
 * tbl_useraccount row converted in place into the permanent super admin
 * (AccountRole = 'super admin', IsSuperAdmin = true) and the .env login stops
 * working.
 *
 * RELEASE: the claimed super admin (session('is_super_admin')) can hand the
 * account back to the .env bootstrap by re-entering their current password;
 * the row is reverted to its pristine unclaimed state so the .env email /
 * password works again.
 */
class SuperAdminSetupController extends Controller
{
    use ManagesOtp;

    /** Session key prefix for the setup email-verification code. */
    protected string $otpPrefix = 'super_admin_verify';

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    /**
     * Only a locked bootstrap session may be here, and only while the .env
     * credentials still exist. Returns a redirect/abort response to bail with,
     * or null to continue.
     */
    protected function guard(): ?\Symfony\Component\HttpFoundation\Response
    {
        if (!session('super_admin_setup')) {
            return session('user_id')
                ? redirect()->route('dashboard')
                : redirect()->route('login');
        }

        if (!config('superadmin.email') || !config('superadmin.password')) {
            abort(404);
        }

        return null;
    }

    public function show(Request $request)
    {
        if ($r = $this->guard()) {
            return $r;
        }

        // Step 2 (enter the emailed code) is shown once a code has been sent,
        // unless the user asked to go back and edit their email/password.
        $showVerify = session('show_super_admin_verify')
            && session('super_admin_setup_email')
            && !$request->boolean('edit');

        return view('auth.super-admin-setup', [
            'bootstrapEmail' => config('superadmin.email'),
            'bootstrapPassword' => config('superadmin.password'),
            'roleLabel' => 'Super Admin',
            'showVerify' => $showVerify,
            'pendingEmail' => session('super_admin_setup_email'),
        ]);
    }

    /**
     * Step 1: validate the chosen email + password, stash them in the session
     * and email a verification code to the new address. Nothing is written to
     * the account until the code is confirmed in verify().
     */
    public function sendCode(Request $request)
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $data = $request->validate([
            'new_email' => [
                'required', 'email', 'max:255',
                'different:' . config('superadmin.email'),
                'unique:tbl_useraccount,Email',
            ],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'new_email.different' => 'Choose an email that isn\'t the setup address.',
            'new_email.unique' => 'That email is already used by another account.',
        ]);

        $throttleKey = 'super-admin-verify:' . session('user_id');
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('superAdminSetup')
                ->with('error', 'Too many code requests. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }
        RateLimiter::hit($throttleKey, 600);

        // Store the password already hashed — it never sits in the session as
        // plain text.
        session([
            'super_admin_setup_email' => $data['new_email'],
            'super_admin_setup_password' => Hash::make($data['password']),
            'super_admin_verify_attempts' => 0,
            'show_super_admin_verify' => true,
        ]);

        $code = $this->issueOtp($this->otpPrefix);
        Mail::to($data['new_email'])->send(new OtpMail($code));

        return redirect()->route('superAdminSetup')->with('verify_sent', true);
    }

    /**
     * "Didn't get the code? Resend."
     */
    public function resendCode(Request $request)
    {
        if ($r = $this->guard()) {
            return $r;
        }

        if (!session('super_admin_setup_email')) {
            return redirect()->route('superAdminSetup');
        }

        if ($this->otpResendTooSoon($this->otpPrefix)) {
            $wait = $this->otpWaitLabel($this->otpResendRetryAfter($this->otpPrefix));
            return redirect()->route('superAdminSetup')
                ->with('error', "Please wait {$wait} before requesting another code.");
        }

        $throttleKey = 'super-admin-verify:' . session('user_id');
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('superAdminSetup')
                ->with('error', 'Too many code requests. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }
        RateLimiter::hit($throttleKey, 600);

        session(['super_admin_verify_attempts' => 0, 'show_super_admin_verify' => true]);

        $code = $this->issueOtp($this->otpPrefix);
        Mail::to(session('super_admin_setup_email'))->send(new OtpMail($code));

        return redirect()->route('superAdminSetup')->with('verify_sent', true);
    }

    /**
     * Step 2: confirm the emailed code, and only then convert the provisioned
     * row into the permanent super admin.
     */
    public function verify(Request $request)
    {
        if ($r = $this->guard()) {
            return $r;
        }

        $request->validate(['code' => 'required|string']);

        if (!session('super_admin_setup_email') || !session()->has($this->otpPrefix . '_code')) {
            return redirect()->route('superAdminSetup', ['edit' => 1])
                ->with('error', 'Your verification code expired. Please start again.');
        }

        if ($this->otpExpired($this->otpPrefix)) {
            $this->clearOtp($this->otpPrefix);
            return redirect()->route('superAdminSetup')
                ->with('error', 'Your verification code expired. Please request a new one.');
        }

        if (!$this->otpMatches($this->otpPrefix, $request->code)) {
            $attempts = session('super_admin_verify_attempts', 0) + 1;

            if ($attempts >= 5) {
                $this->clearOtp($this->otpPrefix);
                session()->forget('super_admin_verify_attempts');
                return redirect()->route('superAdminSetup')
                    ->with('error', 'Too many incorrect attempts. Please request a new code.');
            }

            session(['super_admin_verify_attempts' => $attempts, 'show_super_admin_verify' => true]);

            return redirect()->route('superAdminSetup')
                ->with('error', 'Incorrect code. Please try again.');
        }

        $user = UserAccount::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login')
                ->with('login_error', 'Setup account is missing. Please contact your developer.');
        }

        $email = session('super_admin_setup_email');

        $user->Email = $email;
        $user->Password = session('super_admin_setup_password'); // already hashed
        $user->EmailVerifiedAt = now();
        $user->IsSuperAdmin = true;
        $user->AccountRole = 'super admin';
        $user->AccountType = 'User';
        $user->IsArchived = false;
        $user->save();

        $this->activityLog->log(
            'Super Admin Activated',
            'Bootstrap super admin account set up — email verified and converted to a permanent super admin (' . $email . ').',
            $user->UserID
        );
        $this->activityLog->endSession(session('activity_log_id'));

        $this->clearOtp($this->otpPrefix);
        $request->session()->forget([
            'user_id', 'user_role', 'user_email', 'account_type', 'activity_log_id',
            'is_super_admin', 'super_admin_setup', 'super_admin_verify_attempts',
            'show_super_admin_verify', 'super_admin_setup_email', 'super_admin_setup_password',
        ]);
        $request->session()->regenerate();

        return redirect()->route('login')
            ->with('success', 'Your super admin account is ready. Sign in with your new email and password.');
    }

    /* =====================================================================
     |  RELEASE — hand the claimed account back to the .env bootstrap.
     |  Same two-step shape as setup: email a code to the super admin's
     |  current address, and only revert the row once that code checks out.
     | ===================================================================== */

    /** Session key prefix for the release email-verification code. */
    protected string $releaseOtpPrefix = 'super_admin_release';

    /**
     * Only the claimed super admin may release the account, and only while
     * the .env bootstrap is still configured (otherwise there'd be no way
     * back in). Returns a redirect to bail with, or null to continue.
     */
    protected function guardRelease(): ?\Symfony\Component\HttpFoundation\Response
    {
        if (!session('is_super_admin') || !session('user_id')) {
            return redirect()->route('dashboard');
        }

        if (!config('superadmin.email') || !config('superadmin.password')) {
            return redirect()->route('staffProfile', ['tab' => 'security'])
                ->with('error', 'The setup credentials are not configured, so this account can\'t be released.');
        }

        $user = UserAccount::find(session('user_id'));
        if (!$user || !$user->IsSuperAdmin) {
            return redirect()->route('dashboard');
        }

        return null;
    }

    /**
     * Step 1: email a 6-digit code to the super admin's current address.
     * Nothing about the account changes until the code is confirmed.
     */
    public function sendReleaseCode(Request $request)
    {
        if ($r = $this->guardRelease()) {
            return $r;
        }

        $user = UserAccount::find(session('user_id'));

        if ($this->otpResendTooSoon($this->releaseOtpPrefix)) {
            $wait = $this->otpWaitLabel($this->otpResendRetryAfter($this->releaseOtpPrefix));
            return redirect()->route('staffProfile', ['tab' => 'security'])
                ->with('show_super_admin_release', true)
                ->with('error', "Please wait {$wait} before requesting another code.");
        }

        $throttleKey = 'super-admin-release:' . $user->UserID;
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('staffProfile', ['tab' => 'security'])
                ->with('error', 'Too many code requests. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }
        RateLimiter::hit($throttleKey, 600);

        session(['super_admin_release_attempts' => 0, 'show_super_admin_release' => true]);

        $code = $this->issueOtp($this->releaseOtpPrefix);
        Mail::to($user->Email)->send(new OtpMail($code));

        return redirect()->route('staffProfile', ['tab' => 'security'])->with('verify_sent', true);
    }

    /**
     * Step 2: confirm the emailed code, then revert the row to the pristine
     * "unclaimed bootstrap" state and sign the session out.
     */
    public function verifyRelease(Request $request)
    {
        if ($r = $this->guardRelease()) {
            return $r;
        }

        $request->validate(['code' => 'required|string']);

        $user = UserAccount::find(session('user_id'));

        if (!session()->has($this->releaseOtpPrefix . '_code') || $this->otpExpired($this->releaseOtpPrefix)) {
            $this->clearOtp($this->releaseOtpPrefix);
            session()->forget('show_super_admin_release');
            return redirect()->route('staffProfile', ['tab' => 'security'])
                ->with('error', 'Your verification code expired. Please request a new one.');
        }

        if (!$this->otpMatches($this->releaseOtpPrefix, $request->code)) {
            $attempts = session('super_admin_release_attempts', 0) + 1;

            $this->activityLog->log(
                'Failed Super Admin Release',
                'Incorrect verification code entered when releasing the super admin account.',
                $user->UserID
            );

            if ($attempts >= 5) {
                $this->clearOtp($this->releaseOtpPrefix);
                session()->forget(['show_super_admin_release', 'super_admin_release_attempts']);
                return redirect()->route('staffProfile', ['tab' => 'security'])
                    ->with('error', 'Too many incorrect attempts. Please request a new code.');
            }

            session(['super_admin_release_attempts' => $attempts, 'show_super_admin_release' => true]);

            return redirect()->route('staffProfile', ['tab' => 'security'])
                ->with('error', 'Incorrect code. Please try again.');
        }

        // Revert the row to the pristine "unclaimed bootstrap" state — exactly
        // what LoginController::loginAsSuperAdmin's firstOrCreate would produce.
        $releasedFrom = $user->Email;

        $user->Email = config('superadmin.email');
        $user->Password = Hash::make(Str::random(40));
        $user->IsSuperAdmin = false;
        $user->EmailVerifiedAt = now();
        $user->AccountRole = 'admin';
        $user->AccountType = 'User';
        $user->IsArchived = false;
        $user->save();

        $this->activityLog->log(
            'Super Admin Released',
            'Super admin account released back to the setup credentials (was ' . $releasedFrom . ').',
            $user->UserID
        );
        $this->activityLog->endSession(session('activity_log_id'));

        $this->clearOtp($this->releaseOtpPrefix);
        $request->session()->forget([
            'user_id', 'user_role', 'user_email', 'account_type', 'activity_log_id',
            'is_super_admin', 'super_admin_setup',
            'show_super_admin_release', 'super_admin_release_attempts',
        ]);
        $request->session()->regenerate();

        return redirect()->route('login')
            ->with('success', 'Super admin account released. You can sign in again with the setup credentials.');
    }
}
