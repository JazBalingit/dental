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

        $query = Appointment::with(['patientInfo', 'service', 'dentist.staffInfo'])
            ->orderByRaw('ApprovedAt IS NULL')
            ->orderByDesc('ApprovedAt')
            ->orderByDesc('AppointmentDate')
            ->orderBy('AppointmentTime');

        if ($status && in_array($status, ['Pending', 'Approved', 'Declined', 'Completed', 'Cancelled'])) {
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
            'cancelled' => Appointment::where('Status', 'Cancelled')->count(),
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

        $user = $appointment->patientInfo->userAccount ?? null;
        if ($user) {
            $this->notifications->notifyUser(
                $user,
                'Appointment Completed',
                'Your appointment has been marked as completed. Thank you for visiting us!',
                'success',
                $appointment->AppointmentID,
                'Completed'
            );
        }

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

    public function cancel(Request $request, $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])
            ->whereIn('Status', ['Pending', 'Approved'])
            ->find($id);

        if (!$appointment) {
            return redirect()->route('appointments')
                ->with('error', 'That appointment can no longer be cancelled — its status has already changed.');
        }

        $this->releaseAppointmentSlots($appointment);

        $appointment->Status = 'Cancelled';
        $appointment->DeclineReason = $data['reason'];
        $appointment->save();

        $user = $appointment->patientInfo->userAccount ?? null;
        if ($user) {
            $this->notifications->notifyUser(
                $user,
                'Appointment Cancelled',
                'Your appointment has been cancelled by the clinic.',
                'danger',
                $appointment->AppointmentID,
                'Cancelled'
            );
        }

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->notifications->notifyAdmins(
            'Appointment Cancelled',
            "{$patientName}'s appointment has been cancelled.",
            'danger',
            $appointment->AppointmentID,
            'Cancelled'
        );

        $this->activityLog->log('Cancel', "Cancelled {$patientName}'s appointment. Reason: {$data['reason']}");

        return redirect()->route('appointments')->with('success', 'Appointment cancelled.');
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
