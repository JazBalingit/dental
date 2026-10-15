<?php
// Place in: app/Http/Controllers/AppointmentApprovalController.php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Services\AppointmentExpiryService;
use App\Services\AppointmentReminderService;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentApprovalController extends Controller
{
    public function __construct(
        protected NotificationService $notifications,
        protected ActivityLogService $activityLog,
        protected AppointmentExpiryService $appointmentExpiry,
        protected AppointmentReminderService $reminders
    ) {
    }

    public function index(Request $request)
    {
        $this->appointmentExpiry->expireStalePending();

        // Appointment Requests opens on what needs a decision (Pending);
        // choosing "All Status" sends status= (empty) and shows everything.
        $status = $request->has('status') ? $request->query('status') : 'Pending';
        $search = $request->query('search');

        $query = Appointment::with(['patientInfo', 'service', 'schedule', 'dentist.staffInfo', 'attachments', 'pendingReschedule'])->workflowOrder();

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

        $today = now()->format('Y-m-d');

        $stats = [
            'today' => Appointment::whereDate('AppointmentDate', $today)->count(),
            'approved' => Appointment::where('Status', 'Approved')->count(),
            'pending' => Appointment::where('Status', 'Pending')->count(),
            'declined' => Appointment::where('Status', 'Declined')->count(),
        ];

        // Duration choices + the dentist's day at a glance, for each pending
        // request's "Approve" confirmation.
        $pending = $appointments->getCollection()->where('Status', 'Pending');
        $durationOptions = $pending->mapWithKeys(fn ($a) => [$a->AppointmentID => $this->durationOptions($a)]);
        $dayOverviews = $pending->mapWithKeys(fn ($a) => [$a->AppointmentID => $this->slotStates($a)]);

        return $this->panelView('appointment-approval', [
            'durationOptions' => $durationOptions,
            'dayOverviews' => $dayOverviews,
            'appointments' => $appointments,
            'stats' => $stats,
            'status' => $status,
            'search' => $search,
        ]);
    }

    /**
     * Every duration a pending appointment could be approved with, in slots:
     * slots => ['label', 'end' (e.g. "11:30 AM"), 'available', 'reason', 'current'].
     * Shorter is always possible; longer only while the next slots are free
     * (not another booking, not a dentist-unavailable hour) and before closing.
     */
    public function durationOptions(Appointment $appointment): array
    {
        $times = DentistSchedule::slotTimes();
        $start = array_search($appointment->AppointmentTime, $times, true);
        if ($start === false) {
            return [];
        }

        $current = $appointment->duration_slots;
        $states = $this->slotStates($appointment);

        $options = [];
        $blockedReason = null;
        for ($slots = 1; $start + $slots - 1 < count($times); $slots++) {
            $lastTime = $times[$start + $slots - 1];
            $state = $states[$lastTime];
            // The first busy hour after this visit caps how far it can grow;
            // every longer option says exactly what's in the way.
            if ($slots > $current && $blockedReason === null && !in_array($state['kind'], ['free', 'this'], true)) {
                $blockedReason = $state['reason'];
            }
            $end = Carbon::createFromFormat('H:i', $lastTime)->addMinutes(DentistSchedule::SLOT_MINUTES)->format('g:i A');
            $options[$slots] = [
                'label' => DentistSchedule::formatSlotDuration($slots),
                'end' => $end,
                'available' => $slots <= $current || $blockedReason === null,
                'reason' => $slots > $current ? $blockedReason : null,
                'current' => $slots === $current,
            ];
        }

        return $options;
    }

    /**
     * What every hour of this appointment's day looks like for its dentist:
     * time => ['label', 'kind' (this|free|booked|pending|unavailable|passed),
     * 'reason' (why it can't be used), 'who' (patient, for booked/pending)].
     */
    public function slotStates(Appointment $appointment): array
    {
        $times = DentistSchedule::slotTimes();
        $labels = DentistSchedule::slotLabels();
        $date = $appointment->AppointmentDate->format('Y-m-d');
        $rows = DentistSchedule::where('DentistID', $appointment->DentistID)->where('Date', $date)->get()->keyBy('Time');

        $own = [];
        $start = array_search($appointment->AppointmentTime, $times, true);
        if ($start !== false) {
            $own = array_slice($times, $start, $appointment->duration_slots);
        }

        // time => the other appointment holding it
        $others = [];
        Appointment::with('patientInfo')
            ->where('DentistID', $appointment->DentistID)
            ->whereDate('AppointmentDate', $date)
            ->whereIn('Status', ['Pending', 'Approved'])
            ->whereKeyNot($appointment->AppointmentID)
            ->get()
            ->each(function ($other) use ($times, &$others) {
                $s = array_search($other->AppointmentTime, $times, true);
                if ($s !== false) {
                    foreach (array_slice($times, $s, $other->duration_slots) as $t) {
                        $others[$t] = $other;
                    }
                }
            });

        $states = [];
        foreach ($times as $time) {
            $label = $labels[$time] ?? $time;
            $passed = $date === now()->format('Y-m-d') && Carbon::parse($date . ' ' . $time)->lt(now());
            if (in_array($time, $own, true)) {
                $states[$time] = ['label' => $label, 'kind' => 'this', 'reason' => null, 'who' => null];
            } elseif (isset($others[$time])) {
                $o = $others[$time];
                $range = $o->time_range_label;
                $who = $o->patientInfo ? trim($o->patientInfo->FirstName . ' ' . $o->patientInfo->LastName) : 'another patient';
                $isBooked = $o->Status === 'Approved';
                $states[$time] = ['label' => $label, 'kind' => $isBooked ? 'booked' : 'pending', 'who' => $who,
                    'reason' => $range . ($isBooked ? ' is booked' : ' has a pending request')];
            } elseif (($rows[$time]->Status ?? 'Available') !== 'Available') {
                $states[$time] = ['label' => $label, 'kind' => 'unavailable', 'reason' => $label . ' is unavailable', 'who' => null];
            } elseif ($passed) {
                $states[$time] = ['label' => $label, 'kind' => 'passed', 'reason' => $label . ' has passed', 'who' => null];
            } else {
                $states[$time] = ['label' => $label, 'kind' => 'free', 'reason' => null, 'who' => null];
            }
        }

        return $states;
    }

    public function approve(Request $request, $id)
    {
        $data = $request->validate([
            'duration_slots' => 'nullable|integer|min:1|max:48',
        ]);

        $appointment = Appointment::with(['patientInfo.userAccount'])->find($id);

        if (!$appointment || $appointment->Status !== 'Pending') {
            return redirect()->route('appointmentApproval')
                ->with('error', 'That request is no longer pending — it may have already been actioned.');
        }

        // The slots the booked duration needs were reserved at booking time.
        // Approving can optionally lengthen (claim the next free slots) or
        // shorten (release the tail) the visit — e.g. once the dentist sees
        // the patient's photos. Leaving it unchanged just flips the status.
        $oldSlots = $appointment->duration_slots;
        $newSlots = (int) ($data['duration_slots'] ?? $oldSlots);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($appointment, $oldSlots, $newSlots) {
                if ($newSlots !== $oldSlots) {
                    $this->resizeSlotBlock($appointment, $oldSlots, $newSlots);
                    $appointment->DurationHours = round($newSlots * DentistSchedule::SLOT_MINUTES / 60, 1);
                }
                $appointment->Status = 'Approved';
                $appointment->ApprovedAt = now();
                $appointment->save();
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('appointmentApproval')->with('error', $e->getMessage());
        }

        $changed = $newSlots !== $oldSlots;
        $this->notifyPatient(
            $appointment,
            'Appointment Approved',
            'We are pleased to inform you that your appointment has been approved. We look forward to welcoming you at the clinic.'
                . ($changed ? ' Please note that your dentist adjusted the visit to ' . $appointment->duration_label . ' (' . $appointment->time_range_label . ').' : ''),
            'success'
        );
        $this->notifyAdminsOfStatus($appointment, 'has been approved', 'Approved');

        // If this appointment is already inside a reminder window the moment
        // it's approved (e.g. approved same-day), send that reminder right
        // now instead of waiting for the next scheduled tick — the cron
        // still covers an appointment drifting into its next stage later,
        // purely from time passing with nobody acting.
        $this->reminders->checkOne($appointment);

        $p = $appointment->patientInfo;
        $patientName = $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
        $this->activityLog->log('Approve', "Approved {$patientName}'s appointment."
            . ($changed ? ' Duration adjusted from ' . DentistSchedule::formatSlotDuration($oldSlots) . ' to ' . DentistSchedule::formatSlotDuration($newSlots) . '.' : ''));

        return redirect()->route('appointmentApproval')->with('success', 'Appointment approved.'
            . ($changed ? ' Duration set to ' . $appointment->duration_label . ' (' . $appointment->time_range_label . ').' : ''));
    }

    /** Slot times that OTHER pending/approved appointments with this dentist hold that day. */
    protected function timesHeldByOtherAppointments(Appointment $appointment): array
    {
        $times = DentistSchedule::slotTimes();
        $held = [];
        Appointment::where('DentistID', $appointment->DentistID)
            ->whereDate('AppointmentDate', $appointment->AppointmentDate)
            ->whereIn('Status', ['Pending', 'Approved'])
            ->whereKeyNot($appointment->AppointmentID)
            ->get()
            ->each(function ($other) use ($times, &$held) {
                $start = array_search($other->AppointmentTime, $times, true);
                if ($start !== false) {
                    $held = array_merge($held, array_slice($times, $start, $other->duration_slots));
                }
            });

        return array_values(array_unique($held));
    }

    /**
     * Grow or shrink the block of schedule slots an appointment holds,
     * from $oldSlots to $newSlots (both counted from its start time).
     * Growing locks and claims the next slots, refusing if any is taken or
     * the visit would run past closing. Must run inside a transaction.
     */
    protected function resizeSlotBlock(Appointment $appointment, int $oldSlots, int $newSlots): void
    {
        $times = DentistSchedule::slotTimes();
        $start = array_search($appointment->AppointmentTime, $times, true);
        if ($start === false) {
            throw new \RuntimeException('This appointment\'s start time is not a valid slot, so its duration can\'t be changed.');
        }

        $date = $appointment->AppointmentDate->format('Y-m-d');
        $dentistId = $appointment->DentistID;

        if ($newSlots > $oldSlots) {
            if ($start + $newSlots > count($times)) {
                throw new \RuntimeException('That duration runs past closing time. Please choose a shorter duration.');
            }
            $heldByOthers = $this->timesHeldByOtherAppointments($appointment);
            foreach (array_slice($times, $start + $oldSlots, $newSlots - $oldSlots) as $time) {
                $row = DentistSchedule::where('DentistID', $dentistId)->where('Date', $date)->where('Time', $time)->lockForUpdate()->first();
                if (!$row) {
                    $row = DentistSchedule::create(['DentistID' => $dentistId, 'Date' => $date, 'Time' => $time, 'Status' => 'Available']);
                }
                if ($row->Status !== 'Available' || in_array($time, $heldByOthers, true)) {
                    $label = Carbon::createFromFormat('H:i', $time)->format('g:i A');
                    throw new \RuntimeException("Couldn't extend the visit: {$label} is already taken. Please choose a shorter duration.");
                }
                $row->Status = 'Not Available';
                $row->save();
            }
        } else {
            // Shorter: free the slots this appointment no longer needs.
            DentistSchedule::where('DentistID', $dentistId)->where('Date', $date)
                ->whereIn('Time', array_slice($times, $start + $newSlots, $oldSlots - $newSlots))
                ->update(['Status' => 'Available']);
        }
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
            'We regret to inform you that your appointment request has been declined. You are welcome to book another schedule at your convenience.',
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
        // Account holders get the bell + email; guests and walk-ins with an
        // email get the email (with their manage link).
        $this->notifications->notifyPatient($appointment, $title, $message, $type);
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
