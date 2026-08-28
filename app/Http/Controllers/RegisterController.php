<?php
// Place in: app/Http/Controllers/RegisterController.php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use App\Models\PatientInfo;
use App\Models\SystemSetting;
use App\Models\UserAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class RegisterController extends Controller
{
    public function create()
    {
        return view('login_signup.signup', [
            'privacyPolicy' => SystemSetting::get('privacy_policy_content', ''),
            'legalTerms' => SystemSetting::get('legal_terms_content', ''),
        ]);
    }

    /**
     * Step 1: validate the signup form. If everything (including
     * password === confirm password) checks out, DO NOT save to the
     * database yet — stash it in the session, email a 6-digit code,
     * and show the OTP modal.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'birthdate' => 'required|date|before:today',
            'gender' => 'required|string',
            'religion' => 'nullable|string|max:100',
            'nationality' => 'required|string|max:100',
            'occupation' => 'nullable|string|max:150',
            'address' => 'required|string|max:255',
            'email' => 'required|email|unique:tbl_useraccount,Email',
            'phone' => 'required|string|max:20',
            'guardian_name' => 'nullable|string|max:150',
            'guardian_occupation' => 'nullable|string|max:150',
            // 'confirmed' automatically checks password === password_confirmation
            'password' => 'required|string|min:8|confirmed',
            'agree_terms' => 'accepted',
        ], [
            'agree_terms.accepted' => 'You must agree to the Privacy Policy and Terms to create an account.',
        ]);

        $throttleKey = 'signup-otp:' . strtolower($data['email']);
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('signup')->with('otp_error', "Too many attempts. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        // Age is computed from the birthdate — no manual input needed.
        $age = \Carbon\Carbon::parse($data['birthdate'])->age;

        $pending = [
            'Email' => $data['email'],
            'Password' => Hash::make($data['password']),
            'LastName' => $data['last_name'],
            'FirstName' => $data['first_name'],
            'MiddleName' => $data['middle_name'] ?? null,
            'PhoneNumber' => $data['phone'],
            'DateOfBirth' => $data['birthdate'],
            'Age' => $age,
            'Gender' => $data['gender'],
            'Religion' => $data['religion'] ?? null,
            'Nationality' => $data['nationality'],
            'Address' => $data['address'],
            'Occupation' => $data['occupation'] ?? null,
            'ParentsName' => $data['guardian_name'] ?? null,
            'ParentsOccupation' => $data['guardian_occupation'] ?? null,
        ];

        $code = (string) random_int(100000, 999999);

        session([
            'pending_registration' => $pending,
            'otp_code' => $code,
            'otp_email' => $data['email'],
            'otp_attempts' => 0,
            'show_otp' => true,
        ]);
        session()->forget('otp_error');

        Mail::to($data['email'])->send(new OtpMail($code));

        return redirect()->route('signup')->with('otp_sent', true);
    }

    /**
     * Step 2: check the code the patient typed against the one in session.
     * Only on a correct match do we actually write to tbl_userAcc / tbl_patientInfo.
     */
    public function verify(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        if (!session()->has('pending_registration') || !session()->has('otp_code')) {
            return redirect()->route('signup')->with('otp_expired', true);
        }

        if ($request->code !== session('otp_code')) {
            $attempts = session('otp_attempts', 0) + 1;

            if ($attempts >= 5) {
                session()->forget(['pending_registration', 'otp_code', 'otp_email', 'otp_attempts', 'show_otp']);
                return redirect()->route('signup')->with('otp_expired', true);
            }

            session(['otp_attempts' => $attempts]);

            return redirect()->route('signup')
                ->with('show_otp', true)
                ->with('otp_error', 'Incorrect code. Please try again.');
        }

        $pending = session('pending_registration');

        $user = UserAccount::create([
            'Email' => $pending['Email'],
            'Password' => $pending['Password'],
            'AccountRole' => 'patient',
            'DateCreated' => now(),
        ]);

        PatientInfo::create(array_merge(
            collect($pending)->except(['Email', 'Password'])->toArray(),
            ['UserID' => $user->UserID]
        ));

        session()->forget(['pending_registration', 'otp_code', 'otp_email', 'otp_attempts', 'show_otp']);

        return redirect()->route('login')->with('registered', true);
    }

    /**
     * "Didn't receive the code? Resend" — generates a fresh code,
     * re-sends the email, keeps the modal open.
     */
    public function resend(Request $request)
    {
        if (!session()->has('otp_email')) {
            return redirect()->route('signup')->with('otp_expired', true);
        }

        $throttleKey = 'signup-otp:' . strtolower(session('otp_email'));
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return redirect()->route('signup')
                ->with('show_otp', true)
                ->with('otp_error', "Too many attempts. Please try again in " . ceil($seconds / 60) . " minute(s).");
        }
        RateLimiter::hit($throttleKey, 600);

        $code = (string) random_int(100000, 999999);

        session(['otp_code' => $code, 'otp_attempts' => 0, 'show_otp' => true]);
        session()->forget('otp_error');

        Mail::to(session('otp_email'))->send(new OtpMail($code));

        return redirect()->route('signup')->with('otp_resent', true);
    }

    /**
     * Closing the OTP modal (the X button). Because `show_otp` is a
     * regular session value (not a one-time flash), simply reloading
     * the page would reopen it forever — this explicitly clears it.
     */
    public function cancelOtp(Request $request)
    {
        session()->forget(['pending_registration', 'otp_code', 'otp_email', 'otp_attempts', 'show_otp']);

        return redirect()->route('signup');
    }
}