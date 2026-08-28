<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\PatientRecord;
use App\Services\AppointmentExpiryService;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class AppointmentsController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
        protected AuditLogService $auditLog,
        protected AppointmentExpiryService $appointmentExpiry
    ) {
    }

    protected function guard()
    {
        if (!session('user_id') || session('user_role') !== 'admin') {
            return redirect()->route('login')->with('login_error', 'Please log in as an administrator to continue.');
        }

        return null;
    }

    public function index(Request $request)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $this->appointmentExpiry->expireStalePending();

        $status = $request->query('status');
        $search = $request->query('search');

        $query = Appointment::with(['patientInfo', 'service'])
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

        return view('superAdmin.appointments', [
            'appointments' => $appointments,
            'stats' => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function complete($id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $appointment = Appointment::with(['patientInfo.userAccount', 'service'])->where('Status', 'Approved')->findOrFail($id);

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

        $this->auditLog->log('Complete', "Marked {$patientName}'s appointment as completed.");

        return redirect()->route('appointments')->with('success', 'Appointment marked as completed.');
    }

    public function cancel(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])
            ->whereIn('Status', ['Pending', 'Approved'])
            ->findOrFail($id);

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

        $this->auditLog->log('Cancel', "Cancelled {$patientName}'s appointment. Reason: {$data['reason']}");

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
                ->whereDate('AppointmentDate', $appointment->AppointmentDate)
                ->whereIn('Status', ['Pending', 'Approved'])->get()
                ->contains(function ($other) use ($times, $time) {
                    $otherStart = array_search($other->AppointmentTime, $times, true);
                    $otherDuration = $other->duration_slots;
                    return $otherStart !== false && in_array($time, array_slice($times, $otherStart, $otherDuration), true);
                });
            if (!$stillHeld) {
                DentistSchedule::where('Date', $appointment->AppointmentDate->format('Y-m-d'))->where('Time', $time)->update(['Status' => 'Available']);
            }
        }
    }
}
