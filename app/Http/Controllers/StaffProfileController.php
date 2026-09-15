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

        return view('staff.staff-userprofile', [
            'staff' => $staff,
            'activeTab' => $activeTab,
            // Only offered to the super admin, and only when the .env bootstrap
            // is still configured (there must be a way back in after a release).
            'bootstrapEmail' => config('superadmin.email'),
            'bootstrapConfigured' => (bool) config('superadmin.email') && (bool) config('superadmin.password'),
            'staffVerifyRetrySeconds' => $this->otpResendRetryAfter('staff_verify'),
            'releaseRetrySeconds' => $this->otpResendRetryAfter('super_admin_release'),
        ]);
    }

    /**
     * A staff/dentist/admin editing their own Personal Information. The
     * super admin never reaches this — they have no StaffInfo row at all
     * (see currentStaff()/edit(): $si is null and the edit form doesn't
     * render), so there's nothing here for them to submit.
     */
    public function updateProfile(Request $request)
    {
        $staff = $this->currentStaff();
        $si = $staff->staffInfo;

        if (!$si) {
            abort(404);
        }

        $nameRule = "regex:/^[\pL\s'.-]+$/u";

        $data = $request->validate([
            'last_name' => ['required', 'string', 'max:100', $nameRule],
            'first_name' => ['required', 'string', 'max:100', $nameRule],
            'middle_name' => ['nullable', 'string', 'max:100', $nameRule],
            'birthdate' => 'required|date|before_or_equal:' . now()->subYears(18)->year . '-12-31',
            'gender' => 'required|string',
            'religion' => ['nullable', 'string', 'max:100', $nameRule],
            'nationality' => ['required', 'string', 'max:100', $nameRule],
            'phone' => 'required|digits:11',
            'address' => 'required|string|max:255',
        ]);

        $si->update([
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'DateOfBirth' => $data['birthdate'],
            'Age' => \Carbon\Carbon::parse($data['birthdate'])->age,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'PhoneNumber' => $data['phone'],
            'Address' => $data['address'],
        ]);

        $this->activityLog->log('Profile Updated', 'Updated own profile information (My Profile).', $staff->UserID);

        return redirect()->route('staffProfile')->with('success', 'Profile updated successfully.');
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
                ->withInput($request->only('code'))
                ->with('show_staff_verify', true)
                ->with('email_verify_error', 'Incorrect code. Please try again.');
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
                ->withInput()
                ->with('password_error', 'Your current password is incorrect.');
        }

        $staff->Password = Hash::make($data['password']);
        $staff->save();

        $this->activityLog->log('Password Changed', 'Changed own account password (My Profile → Security).', $staff->UserID);

        return redirect()->route('staffProfile')->with('success', 'Password updated successfully.');
    }
}
