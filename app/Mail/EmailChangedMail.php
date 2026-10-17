<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Sent to the OLD address after an account's email was changed. */
class EmailChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $newEmail,
        public bool $changedBySelf = true,
    ) {
    }

    public function build()
    {
        return $this->subject('Your Account Email Was Changed - Pus-Pus Britanico Dental Clinic')
            ->view('emails.email-changed');
    }
}
