<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $subjectLine,
        public string $body,
        public string $accountNote,
    ) {
    }

    public function build()
    {
        return $this->subject('Contact Us: ' . $this->subjectLine)
            ->replyTo(new Address($this->senderEmail, $this->senderName))
            ->view('emails.contact-message');
    }
}
