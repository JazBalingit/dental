<?php
// Place in: app/Http/Controllers/SettingsController.php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function edit()
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        return view('users.settings');
    }

    /**
     * Standard "Update Password" card — requires the current password,
     * no email/OTP involved since they're already logged in.
     */
    public function updatePassword(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = UserAccount::findOrFail(session('user_id'));

        if (!Hash::check($data['current_password'], $user->Password)) {
            return redirect()->route('settings')->with('password_error', 'Your current password is incorrect.');
        }

        $user->Password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('settings')->with('password_updated', true);
    }

    // ============ "Forgot Password" card, same OTP pattern as login ============
    // Only difference: no email input needed, we already know who's logged in.

    public function sendResetCode(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $code = (string) random_int(100000, 999999);

        session([
            'settings_reset_code' => $code,
            'show_settings_reset_modal' => true,
        ]);
        session()->forget('settings_reset_error');

        Mail::to(session('user_email'))->send(new OtpMail($code));

        return redirect()->route('settings')->with('settings_reset_sent', true);
    }

    public function resendResetCode(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $code = (string) random_int(100000, 999999);

        session(['settings_reset_code' => $code, 'show_settings_reset_modal' => true]);
        session()->forget('settings_reset_error');

        Mail::to(session('user_email'))->send(new OtpMail($code));

        return redirect()->route('settings')->with('settings_reset_resent', true);
    }

    public function verifyAndReset(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!session()->has('settings_reset_code')) {
            return redirect()->route('settings')->with('settings_reset_expired', true);
        }

        if ($request->code !== session('settings_reset_code')) {
            return redirect()->route('settings')
                ->with('show_settings_reset_modal', true)
                ->with('settings_reset_error', 'Incorrect code. Please try again.');
        }

        $user = UserAccount::findOrFail(session('user_id'));
        $user->Password = Hash::make($request->password);
        $user->save();

        session()->forget(['settings_reset_code', 'show_settings_reset_modal']);

        return redirect()->route('settings')->with('password_updated', true);
    }

    /**
     * Closing the reset modal (the X button) on the settings page.
     */
    public function cancelReset(Request $request)
    {
        session()->forget(['settings_reset_code', 'show_settings_reset_modal']);

        return redirect()->route('settings');
    }
}