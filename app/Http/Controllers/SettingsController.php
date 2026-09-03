<?php
// Place in: app/Http/Controllers/SettingsController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesOtp;
use App\Mail\OtpMail;
use App\Models\ActivityLog;
use App\Models\PatientInfo;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    use ManagesOtp;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    /**
     * Renders the merged Settings page. Three tabs:
     *  - "User Information" (formerly the standalone Profile page)
     *  - "Security" (change password)
     *  - "Configuration" — a read-only view of this patient's own slice of
     *    the system activity trail (their logins/logouts, failed login
     *    attempts on their account, password changes, appointments booked /
     *    cancelled / rescheduled, and profile edits).
     * $activeTab picks which one is shown on load.
     */
    public function edit(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $user = UserAccount::findOrFail(session('user_id'));
        $patientInfo = PatientInfo::where('UserID', $user->UserID)->firstOrFail();
        $activeTab = in_array($request->query('tab'), ['security', 'configuration'], true)
            ? $request->query('tab')
            : 'profile';

        $search = $request->query('search');
        $type = $request->query('type');

        $logs = ActivityLog::where('UserID', $user->UserID)
            ->where('IsArchived', false)
            ->when($type, fn ($q) => $q->where('ActivityType', $type))
            ->when($search, fn ($q) => $q->where(fn ($w) => $w
                ->where('Description', 'like', "%{$search}%")
                ->orWhere('ActivityType', 'like', "%{$search}%")))
            ->orderByRaw('COALESCE(LoggedInTime, created_at) DESC')
            ->paginate(15, ['*'], 'activity_page')
            ->withQueryString();

        $activityTypes = ActivityLog::where('UserID', $user->UserID)
            ->distinct()->orderBy('ActivityType')->pluck('ActivityType');

        return view('users.settings', [
            'user' => $user,
            'patientInfo' => $patientInfo,
            'activeTab' => $activeTab,
            'logs' => $logs,
            'activitySearch' => $search,
            'activityType' => $type,
            'activityTypes' => $activityTypes,
        ]);
    }

    /**
     * Standard "Update Password" card â€” requires the current password,
     * no email/OTP involved since they're already logged in.
     */
    public function updatePassword(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = UserAccount::findOrFail(session('user_id'));

        if (!Hash::check($data['current_password'], $user->Password)) {
            $this->activityLog->log('Failed Password Change', 'Entered the wrong current password when trying to change it (Settings → Security).', $user->UserID);
            return redirect()->route('settings', ['tab' => 'security'])
                ->withInput($request->only('current_password', 'password', 'password_confirmation'))
                ->with('password_error', 'Your current password is incorrect.');
        }

        $user->Password = Hash::make($data['password']);
        $user->save();

        $this->activityLog->log('Password Changed', 'Changed account password (Settings → Security).', $user->UserID);

        return redirect()->route('settings', ['tab' => 'security'])->with('password_updated', true);
    }

    // ============ "Forgot Password" card, same OTP pattern as login ============
    // Only difference: no email input needed, we already know who's logged in.

    public function sendResetCode(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $throttleKey = 'settings-reset:' . session('user_id');
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('settings', ['tab' => 'security'])
                ->with('settings_reset_error', 'Too many reset requests. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }
        RateLimiter::hit($throttleKey, 900);

        $code = $this->issueOtp('settings_reset');
        session([
            'settings_reset_attempts' => 0,
            'show_settings_reset_modal' => true,
        ]);
        session()->forget('settings_reset_error');

        Mail::to(session('user_email'))->send(new OtpMail($code));

        return redirect()->route('settings', ['tab' => 'security'])->with('settings_reset_sent', true);
    }

    public function resendResetCode(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        // Must wait 60 seconds between sends.
        if ($this->otpResendTooSoon('settings_reset')) {
            $wait = $this->otpWaitLabel($this->otpResendRetryAfter('settings_reset'));
            return redirect()->route('settings', ['tab' => 'security'])
                ->with('show_settings_reset_modal', true)
                ->with('settings_reset_error', "Please wait {$wait} before requesting another code.");
        }

        $throttleKey = 'settings-reset:' . session('user_id');
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('settings', ['tab' => 'security'])
                ->with('show_settings_reset_modal', true)
                ->with('settings_reset_error', 'Too many reset requests. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }
        RateLimiter::hit($throttleKey, 900);

        $code = $this->issueOtp('settings_reset');
        session(['settings_reset_attempts' => 0, 'show_settings_reset_modal' => true]);
        session()->forget('settings_reset_error');

        Mail::to(session('user_email'))->send(new OtpMail($code));

        return redirect()->route('settings', ['tab' => 'security'])->with('settings_reset_resent', true);
    }

    public function verifyAndReset(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!session()->has('settings_reset_code')) {
            return redirect()->route('settings', ['tab' => 'security'])->with('settings_reset_expired', true);
        }

        // A reset code is only good for 5 minutes.
        if ($this->otpExpired('settings_reset')) {
            $this->clearOtp('settings_reset');
            session()->forget('show_settings_reset_modal');
            return redirect()->route('settings', ['tab' => 'security'])->with('settings_reset_expired', true);
        }

        if (!$this->otpMatches('settings_reset', $request->code)) {
            $attempts = session('settings_reset_attempts', 0) + 1;
            $this->activityLog->log('Failed Password Change', 'Entered an incorrect email reset code (Settings → Security).', session('user_id'));

            if ($attempts >= 5) {
                $this->clearOtp('settings_reset');
                session()->forget('show_settings_reset_modal');
                return redirect()->route('settings', ['tab' => 'security'])->with('settings_reset_expired', true);
            }

            session(['settings_reset_attempts' => $attempts]);

            return redirect()->route('settings', ['tab' => 'security'])
                ->withInput($request->only('password', 'password_confirmation'))
                ->with('show_settings_reset_modal', true)
                ->with('settings_reset_error', 'Incorrect code. Please try again.');
        }

        $user = UserAccount::findOrFail(session('user_id'));
        $user->Password = Hash::make($request->password);
        $user->save();

        $this->activityLog->log('Password Changed', 'Changed account password using an email reset code (Settings → Security).', $user->UserID);

        $this->clearOtp('settings_reset');
        session()->forget('show_settings_reset_modal');

        return redirect()->route('settings', ['tab' => 'security'])->with('password_updated', true);
    }

    /**
     * Closing the reset modal (the X button) on the settings page.
     */
    public function cancelReset(Request $request)
    {
        $this->clearOtp('settings_reset');
        session()->forget('show_settings_reset_modal');

        return redirect()->route('settings', ['tab' => 'security']);
    }
}