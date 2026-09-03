<?php
// Place in: app/Http/Controllers/AppointmentApprovalController.php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Services\AppointmentExpiryService;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentApprovalController extends Controller
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

        $query = Appointment::with(['patientInfo', 'service', 'schedule', 'dentist.staffInfo'])
            ->orderByDesc('created_at');

        if ($status && in_array($status, ['Pending', 'Approved', 'Declined', 'Completed'])) {
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

        $today = now()->format('Y-m-d');

        $stats = [
            'today' => Appointment::whereDate('AppointmentDate', $today)->count(),
            'approved' => Appointment::where('Status', 'Approved')->count(),
            'pending' => Appointment::where('Status', 'Pending')->count(),
            'declined' => Appointment::where('Status', 'Declined')->count(),
        ];

        return $this->panelView('appointment-approval', [
            'appointments' => $appointments,
            'stats' => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $appointment = Appointment::with(['patientInfo.userAccount'])->find($id);

        if (!$appointment || $appointment->Status !== 'Pending') {
            return redirect()->route('appointmentApproval')
                ->with('error', 'That request is no longer pending — it may have already been actioned.');
        }

        // The full block of slots this appointment needs was already
        // reserved (Not Available) the moment it was booked — DurationHours
        // is computed from its services at that point, not chosen here.
        // Approving just flips the status; nothing to (re)block.
        $appointment->Status = 'Approved';
        $appointment->ApprovedAt = now();
        $appointment->save();

        $this->notifyPatient($appointment, 'Appointment Approved', 'Your appointment has been approved.', 'success');
        $this->notifyAdminsOfStatus($appointment, 'has been approved', 'Approved');

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->activityLog->log('Approve', "Approved {$patientName}'s appointment.");

        return redirect()->route('appointmentApproval')->with('success', 'Appointment approved.');
    }

    public function decline(Request $request, $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])->find($id);

        if (!$appointment || !in_array($appointment->Status, ['Pending', 'Approved'], true)) {
            return redirect()->route('appointmentApproval')
                ->with('error', 'That request is no longer active — it may have already been actioned.');
        }

        $appointment->Status = 'Declined';
        $appointment->DeclineReason = $data['reason'];
        $appointment->save();

        // Release every slot this appointment was holding for its full
        // service-driven duration, not just the starting one.
        $this->releaseSlotBlock($appointment);

        $this->notifyPatient(
            $appointment,
            'Appointment Declined',
            'Your appointment has been declined.',
            'danger'
        );
        $this->notifyAdminsOfStatus($appointment, 'has been declined', 'Declined');

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->activityLog->log('Decline', "Declined {$patientName}'s appointment. Reason: {$data['reason']}");

        return redirect()->route('appointmentApproval')->with('success', 'Appointment declined.');
    }

    /**
     * Frees every slot this appointment was holding for its full
     * DurationHours block, but only where no other Pending/Approved
     * appointment that same day still needs that slot. Same pattern as
     * UserController::releaseAppointmentSlots / AppointmentsController's
     * equivalent — kept local rather than shared, as elsewhere in this app.
     */
    protected function releaseSlotBlock(Appointment $appointment): void
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

    protected function notifyPatient(Appointment $appointment, string $title, string $message, string $type): void
    {
        $user = $appointment->patientInfo->userAccount ?? null;

        if (!$user) {
            return;
        }

        $this->notifications->notifyUser($user, $title, $message, $type, $appointment->AppointmentID, $appointment->Status);
    }

    /**
     * Admin-side notification for an approve/decline action. Deliberately
     * does not include the decline reason — the admin already entered it.
     */
    protected function notifyAdminsOfStatus(Appointment $appointment, string $verbPhrase, string $status): void
    {
        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';

        $this->notifications->notifyAdmins(
            "Appointment {$status}",
            "{$patientName}'s appointment {$verbPhrase}.",
            $status === 'Approved' ? 'success' : 'danger',
            $appointment->AppointmentID,
            $status
        );
    }
}
