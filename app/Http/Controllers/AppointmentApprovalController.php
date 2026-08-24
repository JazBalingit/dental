<?php
// Place in: app/Http/Controllers/AppointmentApprovalController.php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Services\AppointmentExpiryService;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentApprovalController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
        protected AuditLogService $auditLog,
        protected AppointmentExpiryService $appointmentExpiry
    ) {
    }

    // Same ordered slot list used by the Dentist Schedule calendar —
    // needed here to figure out which slots to block when a staff member
    // approves an appointment with a multi-hour duration.
    protected array $slotOrder = ['09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];

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

        $query = Appointment::with(['patientInfo', 'service', 'schedule'])
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

        // Pending rows get a duration cap so admins can't approve a longer
        // block than the day actually has room for — see maxDurationForAppointment().
        $appointments->getCollection()->transform(function ($appt) {
            $appt->maxDurationHours = $appt->Status === 'Pending' ? $this->maxDurationForAppointment($appt) : null;
            return $appt;
        });

        $today = now()->format('Y-m-d');

        $stats = [
            'today' => Appointment::whereDate('AppointmentDate', $today)->count(),
            'approved' => Appointment::where('Status', 'Approved')->count(),
            'pending' => Appointment::where('Status', 'Pending')->count(),
            'declined' => Appointment::where('Status', 'Declined')->count(),
        ];

        return view('superAdmin.appointment-approval', [
            'appointments' => $appointments,
            'stats' => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function approve(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'duration_hours' => 'required|integer|min:1|max:8',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])->findOrFail($id);

        // Re-check against the current state of the day (not just what the
        // admin's page had loaded) so a stale value can't overlap a slot
        // another appointment has since claimed.
        $durationHours = min((int) $data['duration_hours'], $this->maxDurationForAppointment($appointment));

        $appointment->Status = 'Approved';
        $appointment->DurationHours = $durationHours;
        $appointment->ApprovedAt = now();
        $appointment->save();

        $this->blockSubsequentSlots(
            $appointment->AppointmentDate->format('Y-m-d'),
            $appointment->AppointmentTime,
            $durationHours
        );

        $this->notifyPatient($appointment, 'Appointment Approved', 'Your appointment has been approved.', 'success');
        $this->notifyAdminsOfStatus($appointment, 'has been approved', 'Approved');

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->auditLog->log('Approve', "Approved {$patientName}'s appointment.");

        return redirect()->route('appointmentApproval')->with('success', 'Appointment approved.');
    }

    public function decline(Request $request, $id)
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])->findOrFail($id);

        $appointment->Status = 'Declined';
        $appointment->DeclineReason = $data['reason'];
        $appointment->save();

        // Release the single slot that was held while this was pending.
        DentistSchedule::where('ScheduleID', $appointment->ScheduleID)->update(['Status' => 'Available']);

        $this->notifyPatient(
            $appointment,
            'Appointment Declined',
            'Your appointment has been declined.',
            'danger'
        );
        $this->notifyAdminsOfStatus($appointment, 'has been declined', 'Declined');

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->auditLog->log('Decline', "Declined {$patientName}'s appointment. Reason: {$data['reason']}");

        return redirect()->route('appointmentApproval')->with('success', 'Appointment declined.');
    }

    /**
     * How many consecutive hours this appointment may be approved for —
     * capped by the clinic closing at 6:00 PM and by whichever other
     * Pending/Approved appointment starts next that same day, whichever
     * leaves less room. Never above the 8-hour ceiling the dropdown allows.
     */
    protected function maxDurationForAppointment(Appointment $appointment): int
    {
        $closingHour = 18; // 6:00 PM
        $adminDurationCap = 8;
        $startHour = (int) substr($appointment->AppointmentTime, 0, 2);
        $maxByClosing = max(1, $closingHour - $startHour);

        $nextHour = Appointment::whereDate('AppointmentDate', $appointment->AppointmentDate)
            ->whereIn('Status', ['Pending', 'Approved'])
            ->whereKeyNot($appointment->AppointmentID)
            ->get()
            ->map(fn ($a) => (int) substr($a->AppointmentTime, 0, 2))
            ->filter(fn ($h) => $h > $startHour)
            ->sort()
            ->first();

        $maxByNextAppointment = $nextHour !== null ? max(1, $nextHour - $startHour) : $adminDurationCap;

        return min($maxByClosing, $maxByNextAppointment, $adminDurationCap);
    }

    /**
     * Marks the starting slot plus the next (duration - 1) slots on the
     * same day as Not Available — e.g. a 1:00 PM booking approved for
     * 4 hours blocks 1:00, 2:00, 3:00, and 4:00 PM.
     */
    protected function blockSubsequentSlots(string $date, string $startTime, int $durationHours): void
    {
        $startIndex = array_search($startTime, $this->slotOrder);

        if ($startIndex === false) {
            return;
        }

        for ($i = 0; $i < $durationHours; $i++) {
            $index = $startIndex + $i;

            if (!isset($this->slotOrder[$index])) {
                break; // ran past the last slot of the day
            }

            $slot = DentistSchedule::firstOrCreate(
                ['Date' => $date, 'Time' => $this->slotOrder[$index]],
                ['Status' => 'Available']
            );
            $slot->Status = 'Not Available';
            $slot->save();
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
