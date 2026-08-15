<?php
// Place in: app/Http/Controllers/LoginController.php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

        $user = UserAccount::where('Email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->Password)) {
            return redirect()->route('login')->with('login_error', 'Incorrect email or password.');
        }

        $request->session()->regenerate();

        session([
            'user_id' => $user->UserID,
            'user_role' => $user->AccountRole,
            'user_email' => $user->Email,
        ]);

        return $user->AccountRole === 'admin'
            ? redirect()->route('dashboard')
            : redirect()->route('landingPage');
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['user_id', 'user_role', 'user_email']);
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

        $code = (string) random_int(100000, 999999);

        session([
            'reset_email' => $data['email'],
            'reset_code' => $code,
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

        $code = (string) random_int(100000, 999999);

        session(['reset_code' => $code, 'show_reset_form' => true]);
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

        session()->forget(['reset_email', 'reset_code', 'show_reset_form']);

        return redirect()->route('login')->with('password_reset', true);
    }

    /**
     * Closing the forgot-password modal (the X button). Explicitly
     * clears the reset session flags so the modal doesn't reopen on
     * the next page load.
     */
    public function cancelReset(Request $request)
    {
        session()->forget(['reset_email', 'reset_code', 'show_reset_form']);

        return redirect()->route('login');
    }
}
