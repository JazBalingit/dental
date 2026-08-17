<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class StaffProfileController extends Controller
{
    protected function guard()
    {
        if (!session('user_id') || session('account_type') !== 'staff') {
            return redirect()->route('login')->with('login_error', 'Please log in to continue.');
        }

        return null;
    }

    protected function currentStaff(): UserAccount
    {
        return UserAccount::with('staffInfo')->findOrFail(session('user_id'));
    }

    public function edit()
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $staff = $this->currentStaff();

        return view('staff.staff-userprofile', ['staff' => $staff]);
    }

    public function sendVerification(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $staff = $this->currentStaff();

        if ($staff->EmailVerifiedAt) {
            return redirect()->route('staffProfile')->with('success', 'Your email is already verified.');
        }

        $throttleKey = 'staff-verify:' . $staff->UserID;
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('staffProfile')->with('error', "Too many requests. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = (string) random_int(100000, 999999);

        session([
            'staff_verify_code' => $code,
            'staff_verify_attempts' => 0,
            'show_staff_verify' => true,
        ]);

        Mail::to($staff->Email)->send(new OtpMail($code));

        return redirect()->route('staffProfile')->with('verify_sent', true);
    }

    public function verifyEmail(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $request->validate(['code' => 'required|string']);

        if (!session()->has('staff_verify_code')) {
            return redirect()->route('staffProfile')->with('error', 'Your verification code expired. Please request a new one.');
        }

        if ($request->code !== session('staff_verify_code')) {
            $attempts = session('staff_verify_attempts', 0) + 1;

            if ($attempts >= 5) {
                session()->forget(['staff_verify_code', 'staff_verify_attempts', 'show_staff_verify']);
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

        session()->forget(['staff_verify_code', 'staff_verify_attempts', 'show_staff_verify']);

        return redirect()->route('staffProfile')->with('success', 'Email verified! You can now change your password.');
    }

    public function updatePassword(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $staff = $this->currentStaff();

        if (!$staff->EmailVerifiedAt) {
            return redirect()->route('staffProfile')->with('error', 'Please verify your email before changing your password.');
        }

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($data['current_password'], $staff->Password)) {
            return redirect()->route('staffProfile')->with('error', 'Your current password is incorrect.');
        }

        $staff->Password = Hash::make($data['password']);
        $staff->save();

        return redirect()->route('staffProfile')->with('success', 'Password updated successfully.');
    }
}
