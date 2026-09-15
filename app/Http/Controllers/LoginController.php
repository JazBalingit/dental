<?php
// Place in: app/Http/Controllers/LoginController.php

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

class LoginController extends Controller
{
    use ManagesOtp;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function create()
    {
        return view('login_signup.login', [
            'resetRetrySeconds' => $this->otpResendRetryAfter('reset'),
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $throttleKey = 'login:' . strtolower($data['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('login')
                ->withInput($request->only('email', 'password'))
                ->with('login_error', "Too many failed attempts. Please try again in {$seconds} seconds.");
        }

        if ($this->loginAsSuperAdmin($request, $data)) {
            RateLimiter::clear($throttleKey);
            // The bootstrap session can do nothing but claim a real account.
            return redirect()->route('superAdminSetup');
        }

        $user = UserAccount::where('Email', $data['email'])->first();

        // Correct credentials but the account is archived — say so plainly
        // instead of "incorrect email or password", which sends people in
        // circles through the forgot-password flow.
        if ($user && $user->IsArchived && Hash::check($data['password'], $user->Password)) {
            RateLimiter::hit($throttleKey, 60);
            $this->activityLog->failedLogin($data['email'], $user->UserID);
            return redirect()->route('login')
                ->withInput($request->only('email'))
                ->with('login_error', 'This account has been deactivated. Please contact the clinic.');
        }

        if (!$user || $user->IsArchived || !Hash::check($data['password'], $user->Password)) {
            RateLimiter::hit($throttleKey, 60);
            $this->activityLog->failedLogin($data['email'], $user?->UserID);
            return redirect()->route('login')
                ->withInput($request->only('email', 'password'))
                ->with('login_error', 'Incorrect email or password.');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // regenerate() rotates the session ID but keeps existing session data,
        // so a leftover is_super_admin / super_admin_setup flag from an earlier
        // super-admin login in this same browser session would otherwise
        // survive into this completely unrelated account's session and bypass
        // every guard that checks it (EnsureStaffIsVerified, EnsureSuperAdmin,
        // EnsureSuperAdminClaimed).
        session()->forget(['is_super_admin', 'super_admin_setup']);

        session([
            'user_id' => $user->UserID,
            'user_role' => $user->AccountRole,
            'user_email' => $user->Email,
            'account_type' => $user->AccountType === 'Staff' ? 'staff' : 'user',
        ]);

        // The one claimed super admin logs in through this ordinary path; the
        // flag is what unlocks the superAdmin/* views, EnsureSuperAdmin, and
        // the Staff Accounts / Configuration menu. AccountRole stays 'admin'.
        if ($user->IsSuperAdmin) {
            session(['is_super_admin' => true]);
        }

        session(['activity_log_id' => $this->activityLog->startSession($user->UserID)]);

        return in_array($user->AccountRole, UserAccount::ADMIN_ROLES, true)
            ? redirect()->route('dashboard')
            : redirect()->route('userAppointment');
    }

    /**
     * Credentials from config/superadmin.php (.env). Disabled if either is
     * blank. Auth itself still runs against the .env values, never the DB —
     * but the super admin gets (or reuses) a real tbl_useraccount row so
     * that everything keyed to a real UserID — audit logs, activity logs,
     * admin notifications — works for them the same as any other admin
     * instead of being silently skipped. The row's Password is never used
     * to authenticate: whoever holds the .env credentials logs in regardless
     * of what's stored there.
     */
    protected function loginAsSuperAdmin(Request $request, array $data): bool
    {
        $email = config('superadmin.email');
        $password = config('superadmin.password');

        if (!$email || !$password || strcasecmp($data['email'], $email) !== 0 || !hash_equals($password, $data['password'])) {
            return false;
        }

        // Once the bootstrap account has been claimed (a real, verified super
        // admin exists in the DB), the .env login is permanently dead. Falls
        // through to the normal DB lookup, which misses because the claimed
        // row's Email is no longer the bootstrap address.
        if (UserAccount::where('IsSuperAdmin', true)->exists()) {
            return false;
        }

        $account = UserAccount::firstOrCreate(
            ['Email' => $email],
            [
                'Password' => Hash::make(Str::random(40)),
                'AccountRole' => 'admin',
                'AccountType' => 'User',
                'DateCreated' => now(),
                'EmailVerifiedAt' => now(),
                'IsArchived' => false,
            ]
        );

        $request->session()->regenerate();

        session([
            'user_id' => $account->UserID,
            'user_role' => 'admin',
            'user_email' => $email,
            'account_type' => 'user',
            // NOT is_super_admin — this session has no admin-panel power at
            // all. EnsureSuperAdminClaimed locks it to the claim screen until
            // it turns itself into a real super admin.
            'super_admin_setup' => true,
        ]);

        session(['activity_log_id' => $this->activityLog->startSession($account->UserID)]);

        return true;
    }

    public function logout(Request $request)
    {
        $this->activityLog->endSession($request->session()->get('activity_log_id'));

        $request->session()->forget([
            'user_id', 'user_role', 'user_email', 'account_type', 'activity_log_id',
            'is_super_admin', 'super_admin_setup',
            'super_admin_setup_email', 'super_admin_setup_password', 'show_super_admin_verify',
            'super_admin_verify_attempts', 'show_super_admin_release', 'super_admin_release_attempts',
        ]);
        $request->session()->regenerate();

        return redirect()->route('login');
    }

    // ================= FORGOT / RESET PASSWORD =================
    // Same OTP pattern as signup — nothing changes in the DB until
    // the code is verified. All three of these redirect back to
    // /login, where the modal auto-reopens via the `show_reset_form`
    // session flag (see login_signup/login.blade.php).

    /**
     * Step 1: patient submits their email from the "Forgot password?"
     * modal. If it exists, generate a code, email it, and flag the
     * modal to show step 2 (code + new password) on reload.
     */
    public function sendResetCode(Request $request)
    {
        // Only an ACTIVE account can reset — an archived one can't log in
        // afterwards anyway, so letting it through just leaves the user
        // "successfully" changing a password that then fails at login.
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:tbl_useraccount,Email,IsArchived,0'],
        ], [
            'email.exists' => 'We couldn\'t find an active account with that email address.',
        ]);

        $throttleKey = 'reset-send:' . strtolower($data['email']);
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('login')->with('reset_error', "Too many reset requests. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        session([
            'reset_email' => $data['email'],
            'reset_attempts' => 0,
            'show_reset_form' => true,
        ]);
        $code = $this->issueOtp('reset');
        session()->forget('reset_error');

        Mail::to($data['email'])->send(new OtpMail($code));

        return redirect()->route('login')->with('reset_sent', true);
    }

    /**
     * "Didn't receive the code? Resend" inside the modal.
     */
    public function resendResetCode(Request $request)
    {
        if (!session()->has('reset_email')) {
            return redirect()->route('login')->with('reset_expired', true);
        }

        // Account could have been archived between requesting and resending.
        if (!UserAccount::where('Email', session('reset_email'))->where('IsArchived', false)->exists()) {
            session()->forget(['reset_email', 'reset_attempts', 'show_reset_form']);
            $this->clearOtp('reset');
            return redirect()->route('login')->with('reset_expired', true);
        }

        // Must wait 60 seconds between sends.
        if ($this->otpResendTooSoon('reset')) {
            $wait = $this->otpWaitLabel($this->otpResendRetryAfter('reset'));
            return redirect()->route('login')
                ->with('show_reset_form', true)
                ->with('reset_error', "Please wait {$wait} before requesting another code.");
        }

        $throttleKey = 'reset-send:' . strtolower(session('reset_email'));
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('login')
                ->with('show_reset_form', true)
                ->with('reset_error', "Too many reset requests. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = $this->issueOtp('reset');

        session(['reset_attempts' => 0, 'show_reset_form' => true]);
        session()->forget('reset_error');

        Mail::to(session('reset_email'))->send(new OtpMail($code));

        return redirect()->route('login')->with('reset_resent', true);
    }

    /**
     * Step 2: verify the code, and only on a correct match, actually
     * update the password in tbl_useraccount.
     */
    public function resetPassword(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'code' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return redirect()->route('login')
                ->withErrors($validator)
                ->with('show_reset_form', true);
        }

        if (!session()->has('reset_email') || !session()->has('reset_code')) {
            return redirect()->route('login')->with('reset_expired', true);
        }

        // A reset code is only good for 5 minutes.
        if ($this->otpExpired('reset')) {
            session()->forget(['reset_email', 'reset_attempts', 'show_reset_form']);
            $this->clearOtp('reset');
            return redirect()->route('login')->with('reset_expired', true);
        }

        if (!$this->otpMatches('reset', $request->code)) {
            $attempts = session('reset_attempts', 0) + 1;

            $wrongCodeUser = UserAccount::where('Email', session('reset_email'))->first();
            $this->activityLog->log('Failed Password Change', "Incorrect reset code entered for \"" . session('reset_email') . "\".", $wrongCodeUser?->UserID);

            if ($attempts >= 5) {
                session()->forget(['reset_email', 'reset_attempts', 'show_reset_form']);
                $this->clearOtp('reset');
                return redirect()->route('login')->with('reset_expired', true);
            }

            session(['reset_attempts' => $attempts]);

            return redirect()->route('login')
                ->withInput()
                ->with('show_reset_form', true)
                ->with('reset_error', 'Incorrect code. Please try again.');
        }

        $user = UserAccount::where('Email', session('reset_email'))
            ->where('IsArchived', false)
            ->first();

        if (!$user) {
            session()->forget(['reset_email', 'reset_attempts', 'show_reset_form']);
            $this->clearOtp('reset');
            return redirect()->route('login')->with('reset_expired', true);
        }

        $user->Password = Hash::make($request->password);
        $user->save();

        $this->activityLog->log('Password Changed', 'Reset password using an email verification code (from the login page).', $user->UserID);

        session()->forget(['reset_email', 'reset_attempts', 'show_reset_form']);
        $this->clearOtp('reset');

        return redirect()->route('login')->with('password_reset', true);
    }

    /**
     * Closing the forgot-password modal (the X button). Explicitly
     * clears the reset session flags so the modal doesn't reopen on
     * the next page load.
     */
    public function cancelReset(Request $request)
    {
        session()->forget(['reset_email', 'reset_attempts', 'show_reset_form']);
        $this->clearOtp('reset');

        return redirect()->route('login');
    }
}
