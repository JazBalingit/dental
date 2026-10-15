<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientRecord;
use App\Services\AppointmentExpiryService;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AppointmentsController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
        protected ActivityLogService $activityLog,
        protected AppointmentExpiryService $appointmentExpiry
    ) {
    }

    public function index(Request $request)
    {
        $this->appointmentExpiry->expireStalePending();

        $status = $request->query('status');
        $search = $request->query('search');

        $query = Appointment::with(['patientInfo.userAccount', 'service', 'dentist.staffInfo', 'attachments', 'pendingReschedule'])->workflowOrder();

        if ($status && in_array($status, ['Pending', 'Approved', 'Declined', 'Completed', 'Cancelled', 'No Show'])) {
            $query->where('Status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('patientInfo', function ($p) use ($search) {
                    $p->where('FirstName', 'like', "%{$search}%")
                        ->orWhere('LastName', 'like', "%{$search}%");
                })->orWhereHas('service', function ($s) use ($search) {
                    $s->where('ServiceName', 'like', "%{$search}%");
                });
            });
        }

        $appointments = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Appointment::count(),
            'approved' => Appointment::where('Status', 'Approved')->count(),
            'completed' => Appointment::where('Status', 'Completed')->count(),
            'pending' => Appointment::where('Status', 'Pending')->count(),
            'declined' => Appointment::where('Status', 'Declined')->count(),
            'cancelled' => Appointment::where('Status', 'Cancelled')->count(),
            'noShow' => Appointment::where('Status', 'No Show')->count(),
        ];

        return $this->panelView('appointments', [
            'appointments' => $appointments,
            'stats' => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function complete($id)
    {
        $appointment = Appointment::with(['patientInfo.userAccount', 'service'])->where('Status', 'Approved')->find($id);

        if (!$appointment) {
            return redirect()->route('appointments')
                ->with('error', 'Only an approved appointment can be marked complete — this one\'s status has already changed.');
        }

        // An appointment can only be marked done once it has actually started —
        // i.e. the current date and time has reached its scheduled slot.
        $startsAt = \Carbon\Carbon::parse(
            $appointment->AppointmentDate->format('Y-m-d') . ' ' . $appointment->AppointmentTime
        );

        if (now()->lt($startsAt)) {
            return redirect()->route('appointments')->with(
                'error',
                'This appointment can only be marked as done once it has started (' .
                    $startsAt->format('M j, Y \a\t g:i A') . ').'
            );
        }

        $appointment->Status = 'Completed';
        $appointment->save();

        PatientRecord::create([
            'PatientID' => $appointment->PatientID,
            'AppointmentID' => $appointment->AppointmentID,
            'ServiceID' => $appointment->ServiceID,
            'VisitDate' => $appointment->AppointmentDate,
            'VisitTime' => $appointment->AppointmentTime,
            'Service' => $appointment->TypeOfAppointment ?: ($appointment->service->ServiceName ?? null),
            'Status' => 'Completed',
        ]);

        $this->notifications->notifyPatient(
            $appointment,
            'Appointment Completed',
            'Your appointment has been completed. Thank you for choosing Pus-Pus Britanico Dental Clinic.',
            'success',
            'Completed'
        );

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->notifications->notifyAdmins(
            'Appointment Completed',
            "{$patientName}'s appointment has been marked as completed.",
            'success',
            $appointment->AppointmentID,
            'Completed'
        );

        $this->activityLog->log('Complete', "Marked {$patientName}'s appointment as completed.");

        return redirect()->route('appointments')->with('success', 'Appointment marked as completed.');
    }

    /**
     * What happened to the appointment — picked from the Cancel modal's
     * dropdown. 'reason' says whether the message to the patient is
     * required / optional / not asked for; 'default' is what the patient
     * sees when an optional note is left blank.
     */
    public const CANCEL_OUTCOMES = [
        'clinic' => ['label' => 'Cancelled by the clinic', 'status' => 'Cancelled', 'reason' => 'required', 'default' => null],
        'patient_request' => ['label' => 'Patient asked to cancel', 'status' => 'Cancelled', 'reason' => 'optional', 'default' => 'Cancelled at the patient\'s request.'],
        'dentist_unavailable' => ['label' => 'Dentist unavailable', 'status' => 'Cancelled', 'reason' => 'optional', 'default' => 'The dentist is unavailable for this appointment.'],
        'no_show' => ['label' => 'No Show — patient didn\'t arrive', 'status' => 'No Show', 'reason' => 'none', 'default' => 'The patient did not arrive for this appointment, so it was marked as a no-show.'],
    ];

    public function cancel(Request $request, $id)
    {
        $outcomeKey = $request->input('outcome', 'clinic');
        $outcome = self::CANCEL_OUTCOMES[$outcomeKey] ?? null;
        if (!$outcome) {
            return redirect()->route('appointments')->with('error', 'Please choose what happened to this appointment.');
        }

        $data = $request->validate([
            'reason' => [$outcome['reason'] === 'required' ? 'required' : 'nullable', 'string', 'max:500'],
        ], ['reason.required' => 'Please write a message to the patient explaining the cancellation.']);

        $appointment = Appointment::with(['patientInfo.userAccount'])
            ->whereIn('Status', ['Pending', 'Approved'])
            ->find($id);

        if (!$appointment) {
            return redirect()->route('appointments')
                ->with('error', 'That appointment can no longer be cancelled — its status has already changed.');
        }

        $isNoShow = $outcome['status'] === 'No Show';

        // You can only miss an appointment whose start time has come.
        if ($isNoShow && \Carbon\Carbon::parse($appointment->AppointmentDate->format('Y-m-d') . ' ' . $appointment->AppointmentTime)->isFuture()) {
            return redirect()->route('appointments')
                ->with('error', 'An appointment can only be marked as a no-show once its scheduled time has passed.');
        }

        $reason = $outcome['reason'] === 'none'
            ? $outcome['default']
            : (trim((string) ($data['reason'] ?? '')) ?: $outcome['default']);

        $this->releaseAppointmentSlots($appointment);

        $appointment->Status = $outcome['status'];
        $appointment->DeclineReason = $reason;
        $appointment->save();

        $dateLabel = $appointment->AppointmentDate->format('F j, Y');
        $this->notifications->notifyPatient(
            $appointment,
            $isNoShow ? 'Missed Appointment' : 'Appointment Cancelled',
            $isNoShow
                ? "We missed you at your appointment on {$dateLabel}, so it has been marked as a no-show. You're welcome to book a new appointment at your convenience."
                : 'We regret to inform you that your appointment has been cancelled by the clinic. Please refer to the reason provided, and feel free to book another schedule at your convenience.',
            $isNoShow ? 'warning' : 'danger',
            $outcome['status']
        );

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->notifications->notifyAdmins(
            $isNoShow ? 'Appointment Marked No Show' : 'Appointment Cancelled',
            $isNoShow ? "{$patientName} didn't arrive for their appointment on {$dateLabel} — marked as a no-show." : "{$patientName}'s appointment has been cancelled.",
            $isNoShow ? 'warning' : 'danger',
            $appointment->AppointmentID,
            $outcome['status']
        );

        $this->activityLog->log('Cancel', $isNoShow
            ? "Marked {$patientName}'s appointment on {$dateLabel} as a no-show."
            : "Cancelled {$patientName}'s appointment ({$outcome['label']}). Reason: {$reason}");

        return redirect()->route('appointments')->with('success', $isNoShow ? 'Appointment marked as a no-show.' : 'Appointment cancelled.');
    }

    /**
     * Same slot-release logic as UserController::releaseAppointmentSlots —
     * kept local since it's small and this codebase already duplicates the
     * time-slot array per controller rather than sharing a helper.
     */
    protected function releaseAppointmentSlots(Appointment $appointment): void
    {
        $times = DentistSchedule::slotTimes();
        $start = array_search($appointment->AppointmentTime, $times, true);
        $duration = $appointment->duration_slots;

        for ($offset = 0; $start !== false && $offset < $duration && isset($times[$start + $offset]); $offset++) {
            $time = $times[$start + $offset];
            $stillHeld = Appointment::whereKeyNot($appointment->AppointmentID)
                ->where('DentistID', $appointment->DentistID)
                ->whereDate('AppointmentDate', $appointment->AppointmentDate)
                ->whereIn('Status', ['Pending', 'Approved'])->get()
                ->contains(function ($other) use ($times, $time) {
                    $otherStart = array_search($other->AppointmentTime, $times, true);
                    $otherDuration = $other->duration_slots;
                    return $otherStart !== false && in_array($time, array_slice($times, $otherStart, $otherDuration), true);
                });
            if (!$stillHeld) {
                DentistSchedule::where('DentistID', $appointment->DentistID)
                    ->where('Date', $appointment->AppointmentDate->format('Y-m-d'))
                    ->where('Time', $time)
                    ->update(['Status' => 'Available']);
            }
        }
    }
}
