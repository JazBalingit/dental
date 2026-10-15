<?php
// Place in: app/Mail/OtpMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;

    /** What the code is for, finishing "Use the code below to …". */
    public string $purpose;

    public function __construct(string $code, string $purpose = 'verify your email address and finish creating your account')
    {
        $this->code = $code;
        $this->purpose = $purpose;
    }

    public function build()
    {
        return $this->subject('Your Verification Code - Pus-Pus Britanico Dental Clinic')
            ->view('login_signup.otp');
    }
}
