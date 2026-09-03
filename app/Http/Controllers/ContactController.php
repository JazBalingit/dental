<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\SystemSetting;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    /**
     * Landing-page "Contact Us" form.
     *
     *  - Signed-in patient  → we already know their name + email (from the
     *    account); they only type a subject and message.
     *  - Guest              → they also fill in a name + email.
     *
     * Either way the message is emailed to the clinic address configured in
     * Configuration → System Information (SystemSetting::contactEmail()), with
     * the sender set as Reply-To so staff can reply straight from their inbox.
     */
    public function send(Request $request)
    {
        $loggedIn = (bool) session('user_id');

        $rules = [
            'subject' => 'required|string|max:150',
            'message' => 'required|string|max:3000',
        ];

        if (!$loggedIn) {
            $rules['name'] = 'required|string|max:120';
            $rules['email'] = 'required|email|max:150';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->to(route('landingPage') . '#contact')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Throttle: 5 messages per hour per IP, so the public form can't be
        // used to blast the clinic inbox.
        $throttleKey = 'contact-form:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $minutes = ceil(RateLimiter::availableIn($throttleKey) / 60);
            return redirect()->to(route('landingPage') . '#contact')
                ->with('error', "You've sent several messages recently. Please try again in about {$minutes} minute(s).");
        }
        RateLimiter::hit($throttleKey, 3600);

        // Resolve who this is from.
        if ($loggedIn) {
            $user = UserAccount::with('patientInfo')->find(session('user_id'));
            $info = $user?->patientInfo;
            $senderName = $info ? trim($info->FirstName . ' ' . $info->LastName) : ($user->Email ?? 'Patient');
            $senderEmail = $user->Email ?? session('user_email');
            $accountNote = 'Registered patient account';
        } else {
            $senderName = $data['name'];
            $senderEmail = $data['email'];
            $accountNote = 'Guest (not signed in)';
        }

        Mail::to(SystemSetting::contactEmail())->send(new ContactMessageMail(
            senderName: $senderName,
            senderEmail: $senderEmail,
            subjectLine: $data['subject'],
            body: $data['message'],
            accountNote: $accountNote,
        ));

        $this->activityLog->log(
            'Contact Message',
            "Sent a Contact Us message (\"{$data['subject']}\") from {$senderName} <{$senderEmail}>.",
            $loggedIn ? session('user_id') : null
        );

        return redirect()->to(route('landingPage') . '#contact')
            ->with('success', "Thanks, {$senderName}! Your message has been sent — we'll get back to you soon.");
    }
}
