<?php
// Place in: app/Http/Controllers/LoginController.php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\ActivityLog;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    public function create()
    {
        return view('login_signup.login');
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
            return redirect()->route('login')->with('login_error', "Too many failed attempts. Please try again in {$seconds} seconds.");
        }

        if ($this->loginAsSuperAdmin($request, $data)) {
            RateLimiter::clear($throttleKey);
            return redirect()->route('dashboard');
        }

        $user = UserAccount::where('Email', $data['email'])->first();

        if (!$user || $user->IsArchived || !Hash::check($data['password'], $user->Password)) {
            RateLimiter::hit($throttleKey, 60);
            return redirect()->route('login')->with('login_error', 'Incorrect email or password.');
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        session([
            'user_id' => $user->UserID,
            'user_role' => $user->AccountRole,
            'user_email' => $user->Email,
            'account_type' => $user->AccountType === 'Staff' ? 'staff' : 'user',
        ]);

        $activityLog = ActivityLog::create([
            'UserID' => $user->UserID,
            'ActivityType' => 'Login',
            'LoggedInTime' => now(),
        ]);
        session(['activity_log_id' => $activityLog->ActivityLogsID]);

        return $user->AccountRole === 'admin'
            ? redirect()->route('dashboard')
            : redirect()->route('landingPage');
    }

    /**
     * Credentials from config/superadmin.php (.env) — grants a full admin
     * session with no tbl_useraccount row. Disabled if either is blank.
     * No ActivityLog entry is written since UserID is a foreign key into
     * tbl_useraccount and this session's UserID doesn't exist there.
     */
    protected function loginAsSuperAdmin(Request $request, array $data): bool
    {
        $email = config('superadmin.email');
        $password = config('superadmin.password');

        if (!$email || !$password || strcasecmp($data['email'], $email) !== 0 || !hash_equals($password, $data['password'])) {
            return false;
        }

        $request->session()->regenerate();

        session([
            'user_id' => config('superadmin.user_id'),
            'user_role' => 'admin',
            'user_email' => $email,
            'account_type' => 'staff',
            'is_super_admin' => true,
        ]);

        return true;
    }

    public function logout(Request $request)
    {
        if ($request->session()->has('activity_log_id')) {
            ActivityLog::where('ActivityLogsID', $request->session()->get('activity_log_id'))
                ->update(['LoggedOutTime' => now()]);
        }

        $request->session()->forget(['user_id', 'user_role', 'user_email', 'account_type', 'activity_log_id']);
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
        $data = $request->validate([
            'email' => 'required|email|exists:tbl_useraccount,Email',
        ]);

        $throttleKey = 'reset-send:' . strtolower($data['email']);
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('login')->with('reset_error', "Too many reset requests. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = (string) random_int(100000, 999999);

        session([
            'reset_email' => $data['email'],
            'reset_code' => $code,
            'reset_attempts' => 0,
            'show_reset_form' => true,
        ]);
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

        $throttleKey = 'reset-send:' . strtolower(session('reset_email'));
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('login')
                ->with('show_reset_form', true)
                ->with('reset_error', "Too many reset requests. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = (string) random_int(100000, 999999);

        session(['reset_code' => $code, 'reset_attempts' => 0, 'show_reset_form' => true]);
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
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login')
                ->withErrors($validator)
                ->with('show_reset_form', true);
        }

        if (!session()->has('reset_email') || !session()->has('reset_code')) {
            return redirect()->route('login')->with('reset_expired', true);
        }

        if ($request->code !== session('reset_code')) {
            $attempts = session('reset_attempts', 0) + 1;

            if ($attempts >= 5) {
                session()->forget(['reset_email', 'reset_code', 'reset_attempts', 'show_reset_form']);
                return redirect()->route('login')->with('reset_expired', true);
            }

            session(['reset_attempts' => $attempts]);

            return redirect()->route('login')
                ->with('show_reset_form', true)
                ->with('reset_error', 'Incorrect code. Please try again.');
        }

        $user = UserAccount::where('Email', session('reset_email'))->first();

        if (!$user) {
            return redirect()->route('login')->with('reset_expired', true);
        }

        $user->Password = Hash::make($request->password);
        $user->save();

        session()->forget(['reset_email', 'reset_code', 'reset_attempts', 'show_reset_form']);

        return redirect()->route('login')->with('password_reset', true);
    }

    /**
     * Closing the forgot-password modal (the X button). Explicitly
     * clears the reset session flags so the modal doesn't reopen on
     * the next page load.
     */
    public function cancelReset(Request $request)
    {
        session()->forget(['reset_email', 'reset_code', 'reset_attempts', 'show_reset_form']);

        return redirect()->route('login');
    }
}
