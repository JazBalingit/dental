<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $heading;
    public string $body;

    public function __construct(string $heading, string $body)
    {
        $this->heading = $heading;
        $this->body = $body;
    }

    public function build()
    {
        return $this->subject($this->heading)
            ->view('users.appointment-status');
    }
}
