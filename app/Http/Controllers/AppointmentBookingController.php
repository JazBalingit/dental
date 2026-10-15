<?php
// Place in: app/Http/Controllers/AppointmentBookingController.php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsBookingCalendar;
use App\Models\Appointment;
use App\Models\AppointmentAttachment;
use App\Models\MedicalHistory;
use App\Models\PatientInfo;
use App\Models\Service;
use App\Models\UserAccount;
use App\Services\ActivityLogService;
use App\Services\BookingService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentBookingController extends Controller
{
    use BuildsBookingCalendar;

    public function __construct(protected NotificationService $notifications, protected ActivityLogService $activityLog, protected BookingService $booking)
    {
    }

    public function store(Request $request)
    {
        if (!session('user_id')) {
            return redirect()->route('login')->with('login_error', 'Please log in to book an appointment.');
        }

        $forOther = $request->input('booking_for') === 'other';
        $backTo = route('userAppointment.book', $forOther ? ['for' => 'other'] : []);
        $newPerson = $forOther && $request->input('dependent_id', 'new') === 'new';

        $user = UserAccount::with('patientInfo.latestMedicalHistory')->findOrFail(session('user_id'));
        $self = $user->patientInfo;

        // Medical history of whoever it's for: confirm what's on file, or fill it in.
        $dependent = $forOther && !$newPerson
            ? PatientInfo::dependentsOf($user)->with('latestMedicalHistory')->find((int) $request->input('dependent_id'))
            : null;
        $latestMedical = $forOther ? $dependent?->latestMedicalHistory : $self?->latestMedicalHistory;
        MedicalHistory::takeInput($request, match (true) {
            !$forOther => 'mh',
            $newPerson => 'mh_new',
            default => 'mh_p' . (int) $request->input('dependent_id'),
        });

        $data = $request->validate(array_merge([
            'date' => 'required|date',
            'time' => 'required|string',
            'dentist_id' => 'required|integer',
            'service_ids' => 'required|array|min:1|max:' . Service::MAX_PER_APPOINTMENT,
            'service_ids.*' => 'distinct|exists:tbl_services,ServiceID',
            // Optional photos for the dentist (teeth, X-rays, old records).
            'attachments' => 'nullable|array|max:' . AppointmentAttachment::MAX_FILES,
            'attachments.*' => 'image|mimes:jpg,jpeg,png,webp|max:' . AppointmentAttachment::MAX_KB,
            // At least one of photos / a note is required — whichever the patient has.
            'patient_notes' => 'nullable|string|max:1000|required_without:attachments',
            'booking_for' => 'nullable|in:self,other',
            'dependent_id' => 'nullable',
        ], $newPerson ? BookingService::otherRules() : [],
            MedicalHistory::bookingRules($request->input('mh_status'), $latestMedical)), array_merge(MedicalHistory::messages(), [
            'patient_notes.required_without' => 'Please add a photo or write a note for your dentist so they know what you need.',
            'patient_notes.max' => 'Your note can be up to 1,000 characters.',
            'service_ids.max' => 'You can choose up to ' . Service::MAX_PER_APPOINTMENT . ' services per appointment.',
            'attachments.max' => 'You can attach up to ' . AppointmentAttachment::MAX_FILES . ' photos.',
            'attachments.*.image' => 'Attachments must be photos (JPG, PNG or WebP).',
            'attachments.*.mimes' => 'Attachments must be photos (JPG, PNG or WebP).',
            'attachments.*.max' => 'Each photo must be under 1 MB. Please try a smaller photo.',
        ], BookingService::messages()));

        if (!$self) {
            return redirect()->to($backTo)->with('booking_error', 'Your patient profile is incomplete.');
        }

        $booker = [
            'user_id' => $user->UserID,
            'email' => $user->Email,
            'name' => $self->full_name,
            'phone' => $self->PhoneNumber,
            'for_other' => $forOther,
        ];

        // Who is it for: yourself, someone you've booked for before, or someone new.
        $patient = $self;
        $createPatient = null;
        if ($forOther) {
            if ($newPerson) {
                $patient = null;
                $createPatient = fn () => PatientInfo::create(BookingService::otherAttributes($data, $booker, $self->Address));
            } else {
                $patient = $dependent;
                if (!$patient) {
                    return redirect()->to($backTo)->with('booking_error', 'Please choose who this appointment is for.');
                }
            }
        }

        try {
            $dentist = $this->booking->dentist((int) $data['dentist_id']);
            $appointment = $this->booking->create(
                $patient,
                $dentist,
                $data,
                BookingService::photosFromUploads($request->file('attachments', [])),
                $booker,
                $createPatient,
                fn ($appointment) => $appointment->update(['MedicalHistoryID' => MedicalHistory::forBooking($data, $latestMedical, $appointment->PatientID, 'patient', $user, $self->full_name)->MedicalHistoryID])
            );
        } catch (\Throwable $e) {
            $failureMessage = $e instanceof \RuntimeException
                ? $e->getMessage()
                : 'Could not book this appointment. Please try again.';
            if (!$e instanceof \RuntimeException) {
                report($e);
            }

            // Keep a record of why in the patient's notifications — the
            // flash toast disappears in a few seconds, but this needs
            // no email since they're already looking right at it.
            $this->notifications->notifyUser($user, 'Booking Failed', $failureMessage, 'danger', null, null, null, false);
            $this->activityLog->log('Failed Booking', "Appointment booking failed: {$failureMessage}", $user->UserID);

            return redirect()->to($backTo)->withInput($request->except('attachments'))->with('booking_error', $failureMessage);
        }

        $appointment->load('patientInfo');
        $patientName = $appointment->patientInfo->full_name;
        $timeLabel = Carbon::createFromFormat('H:i', $appointment->AppointmentTime)->format('g:i A');
        $dateLabel = $appointment->AppointmentDate->format('F j, Y');
        $dentistLabel = $dentist->display_name;

        $this->notifications->notifyPatient(
            $appointment,
            'Appointment Booked',
            $forOther
                ? "Your appointment request for {$patientName} has been received and is now pending approval. You will be notified once it has been reviewed."
                : 'Your appointment request has been received and is now pending approval. You will be notified once it has been reviewed.',
            'warning',
            'Pending'
        );

        $this->notifications->notifyAdmins(
            'New Appointment',
            $forOther
                ? "{$booker['name']} has requested an appointment with {$dentistLabel} for {$patientName} ({$appointment->patientInfo->Relationship})."
                : "{$patientName} has requested an appointment with {$dentistLabel}.",
            'info',
            $appointment->AppointmentID,
            'Pending'
        );

        $durationLabel = $appointment->duration_label;
        $serviceList = $appointment->TypeOfAppointment;
        $this->activityLog->log('Appointment Booked', "Booked an appointment with {$dentistLabel} for {$dateLabel} at {$timeLabel} ({$durationLabel}) — {$serviceList}" . ($forOther ? " — for {$patientName}." : '.'), $user->UserID);

        return redirect()->to(route('userAppointment'))
            ->with('booking_success', ($forOther ? "Booked for {$patientName} with {$dentistLabel}! " : "Booked with {$dentistLabel}! ") . "{$dateLabel} at {$timeLabel} for {$durationLabel} — status: Pending.");
    }
}
