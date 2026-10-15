<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $heading;
    public string $body;
    public ?Appointment $appointment;

    /** "Manage my appointment" link — only for people without an account (guests / walk-ins). */
    public ?string $manageUrl;

    public function __construct(string $heading, string $body, ?Appointment $appointment = null, ?string $manageUrl = null)
    {
        $this->heading = $heading;
        $this->body = $body;
        $this->appointment = $appointment;
        $this->manageUrl = $manageUrl;
    }

    /**
     * label => value rows for the "Appointment details" block, so every
     * appointment email states when, what and with whom — not only the
     * one-line message. Empty for emails that aren't about an appointment.
     */
    public function details(): array
    {
        $a = $this->appointment;

        if (!$a) {
            return [];
        }

        $patient = $a->patientInfo ? trim($a->patientInfo->FirstName . ' ' . $a->patientInfo->LastName) : null;
        $services = $a->TypeOfAppointment
            ?: ($a->services->pluck('ServiceName')->implode(', ') ?: ($a->service->ServiceName ?? null));

        $rows = [
            'Patient' => $patient . ($a->BookedForOther && $a->patientInfo?->Relationship ? " ({$a->patientInfo->Relationship})" : ''),
            'Booked by' => $a->booked_by_label,
            'Date' => $a->AppointmentDate?->format('l, F j, Y'),
            'Time' => $a->AppointmentTime ? $a->time_range_label : null,
            'Service' => $services,
            'Dentist' => $a->dentist_name,
            'Duration' => $a->duration_label,
            'Status' => $a->Status === 'Approved' ? 'Approved' : $a->Status,
        ];

        if (in_array($a->Status, ['Declined', 'Cancelled', 'No Show'], true) && $a->DeclineReason) {
            $rows['Reason'] = $a->DeclineReason;
        }

        return array_filter($rows, fn ($v) => $v !== null && $v !== '');
    }

    /** [date, time] of the appointment for the highlighted banner, or null. */
    public function schedule(): ?array
    {
        $a = $this->appointment;

        if (!$a || !$a->AppointmentDate || !$a->AppointmentTime) {
            return null;
        }

        return [$a->AppointmentDate->format('l, F j, Y'), $a->time_range_label];
    }

    public function build()
    {
        $schedule = $this->schedule();

        // The subject carries the appointment's date and start time too, so the
        // inbox preview already answers "when?".
        $subject = $this->heading;
        if ($schedule) {
            $subject .= ' — ' . $this->appointment->AppointmentDate->format('M j, Y') . ', '
                . \Carbon\Carbon::createFromFormat('H:i', $this->appointment->AppointmentTime)->format('g:i A');
        }

        return $this->subject($subject)
            ->view('users.appointment-status', [
                'details' => $this->details(),
                'schedule' => $schedule,
                'forOther' => $this->appointment?->BookedForOther ? $this->appointment->patientInfo?->full_name : null,
                // Guests can turn their booking into an account; only offered
                // while nobody has signed up with that email yet.
                'signupUrl' => $this->manageUrl && $this->appointment?->BookedByEmail
                    && !\App\Models\UserAccount::where('Email', $this->appointment->BookedByEmail)->exists()
                    ? route('signup', ['email' => $this->appointment->BookedByEmail])
                    : null,
            ]);
    }
}
