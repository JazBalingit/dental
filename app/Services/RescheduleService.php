<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use App\Models\RescheduleRequest;
use App\Models\Service;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Everything about moving an appointment to a new time.
 *
 *  Staff propose  → the original slot stays reserved AND the proposed slot is
 *                   held (schedule rows Not Available) so nobody else takes it.
 *  Patient accepts → the appointment moves; the old slot is released.
 *  Patient keeps the original / staff withdraw / it expires → the hold is released.
 *  Patient picks their own time → the appointment moves there as Pending
 *                   (staff approve it again); any open proposal is dropped.
 *
 * Slot rule used everywhere: a time is free only if its schedule row is
 * Available (or missing) AND no other Pending/Approved appointment or open
 * proposal holds it. Rows are locked FOR UPDATE inside a transaction so two
 * people can't grab the same slot at once.
 */
class RescheduleService
{
    public function __construct(
        protected NotificationService $notifications,
        protected ActivityLogService $activityLog,
        protected AppointmentReminderService $reminders
    ) {
    }

    /** Staff propose a new time. Replaces any open proposal for this appointment. */
    public function propose(Appointment $appointment, int $dentistId, string $date, string $time, array $serviceIds, ?string $reason): RescheduleRequest
    {
        $this->assertReschedulable($appointment);
        $dentist = $this->assertDentist($dentistId);
        $services = $this->servicesFor($serviceIds);
        $slots = $this->slotsFor($services);
        $this->assertFuture($date, $time);

        $proposal = DB::transaction(function () use ($appointment, $dentist, $date, $time, $services, $slots, $reason) {
            // A newer proposal replaces the old one (and frees its hold).
            foreach (RescheduleRequest::where('AppointmentID', $appointment->AppointmentID)->where('Status', 'Pending')->lockForUpdate()->get() as $old) {
                $old->update(['Status' => 'Superseded', 'RespondedAt' => now()]);
                $this->release($old->ProposedDentistID, $old->ProposedDate->format('Y-m-d'), $old->held_times);
            }

            $times = $this->claim($dentist->UserID, $date, $time, $slots, $appointment->AppointmentID);

            $startsAt = Carbon::parse($appointment->AppointmentDate->format('Y-m-d') . ' ' . $appointment->AppointmentTime);
            $expires = now()->addHours(RescheduleRequest::RESPONSE_HOURS);
            if ($startsAt->lt($expires)) {
                $expires = $startsAt;
            }

            return RescheduleRequest::create([
                'AppointmentID' => $appointment->AppointmentID,
                'ProposedDentistID' => $dentist->UserID,
                'ProposedDate' => $date,
                'ProposedTime' => $times[0],
                'DurationSlots' => $slots,
                'ServiceIDs' => $services->pluck('ServiceID')->implode(','),
                'Reason' => $reason ? mb_substr(trim($reason), 0, 500) : null,
                'Status' => 'Pending',
                'ProposedBy' => session('user_id'),
                'ExpiresAt' => $expires,
            ]);
        });

        $patientName = $this->patientName($appointment);
        $this->notifications->notifyPatient(
            $appointment,
            'New Time Proposed for Your Appointment',
            "The clinic would like to move your appointment to {$proposal->when_label} with {$dentist->display_name}."
                . ($proposal->Reason ? " Reason: {$proposal->Reason}." : '')
                . ' Open your appointment (Patient Portal → Current Appointments, or the "Manage my appointment" button) to accept it, choose another time, or keep your original schedule. Please respond by '
                . $proposal->ExpiresAt->format('M j, g:i A') . '.',
            'warning'
        );

        $this->activityLog->log('Reschedule Proposed', "Proposed moving {$patientName}'s appointment to {$proposal->when_label} with {$dentist->display_name}."
            . ($proposal->Reason ? " Reason: {$proposal->Reason}" : ''));

        return $proposal;
    }

    /** Patient accepts: the appointment moves to the proposed time (and is approved). */
    public function accept(RescheduleRequest $proposal): Appointment
    {
        $appointment = DB::transaction(function () use ($proposal) {
            $proposal = RescheduleRequest::lockForUpdate()->findOrFail($proposal->RescheduleID);
            $this->assertOpen($proposal);
            $appointment = Appointment::with('patientInfo.userAccount')->lockForUpdate()->findOrFail($proposal->AppointmentID);
            $this->assertReschedulable($appointment);

            $date = $proposal->ProposedDate->format('Y-m-d');
            // The held rows are Not Available on purpose — re-check that no
            // appointment slipped in, then keep them reserved for this visit.
            $this->assertNoOtherHolders($proposal->ProposedDentistID, $date, $proposal->held_times, $appointment->AppointmentID, $proposal->RescheduleID);

            $oldDentist = $appointment->DentistID;
            $oldDate = $appointment->AppointmentDate->format('Y-m-d');
            $oldTimes = $this->timesOf($appointment);

            $this->moveAppointment($appointment, $proposal->ProposedDentistID, $date, $proposal->ProposedTime, $proposal->DurationSlots, $proposal->service_id_list);
            $appointment->Status = 'Approved';
            $appointment->ApprovedAt = $appointment->ApprovedAt ?? now();
            $appointment->save();

            $proposal->update(['Status' => 'Accepted', 'RespondedAt' => now()]);

            $this->release($oldDentist, $oldDate, $oldTimes, $appointment->AppointmentID);

            return $appointment;
        });

        $this->notifications->notifyPatient(
            $appointment,
            'Appointment Moved',
            "You accepted the new time — your appointment is now on {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}.",
            'success'
        );

        $patientName = $this->patientName($appointment);
        $this->notifications->notifyAdmins(
            'Reschedule Accepted',
            "{$patientName} accepted the new time: {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}.",
            'success',
            $appointment->AppointmentID,
            'Approved'
        );
        $this->activityLog->log('Reschedule Accepted', "Accepted the proposed new time: {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}.");

        // It's Approved at the new time now — send the reminder for the stage
        // it's already in (e.g. "in 5 hours" if the new time is today) right
        // away, the same as approving an appointment does, instead of waiting
        // for the scheduler. Later stages follow from the schedule as usual.
        // Accounts get bell + email, guests get it by email (notifyPatient).
        $this->reminders->checkOne($appointment->fresh());

        return $appointment;
    }

    /** Patient keeps the original time: the proposal is dropped and its hold released. */
    public function decline(RescheduleRequest $proposal): void
    {
        DB::transaction(function () use ($proposal) {
            $proposal = RescheduleRequest::lockForUpdate()->findOrFail($proposal->RescheduleID);
            $this->assertOpen($proposal);
            $proposal->update(['Status' => 'Declined', 'RespondedAt' => now()]);
            $this->release($proposal->ProposedDentistID, $proposal->ProposedDate->format('Y-m-d'), $proposal->held_times);
        });

        $appointment = $proposal->appointment()->with('patientInfo')->first();
        $patientName = $this->patientName($appointment);
        $this->notifications->notifyAdmins(
            'Reschedule Declined',
            "{$patientName} kept their original time ({$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}) instead of the proposed one.",
            'info',
            $appointment->AppointmentID,
            $appointment->Status
        );
        $this->activityLog->log('Reschedule Declined', "Kept the original appointment time instead of the proposed {$proposal->when_label}.");
    }

    /** Staff take back an open proposal. */
    public function withdraw(RescheduleRequest $proposal): void
    {
        DB::transaction(function () use ($proposal) {
            $proposal = RescheduleRequest::lockForUpdate()->findOrFail($proposal->RescheduleID);
            $this->assertOpen($proposal);
            $proposal->update(['Status' => 'Withdrawn', 'RespondedAt' => now()]);
            $this->release($proposal->ProposedDentistID, $proposal->ProposedDate->format('Y-m-d'), $proposal->held_times);
        });

        $appointment = $proposal->appointment()->with('patientInfo.userAccount')->first();
        $this->notifications->notifyPatient(
            $appointment,
            'Reschedule Withdrawn',
            "The clinic withdrew the proposed new time ({$proposal->when_label}). Your appointment stays on {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}.",
            'info'
        );
        $this->activityLog->log('Edit', "Withdrew the proposed new time ({$proposal->when_label}) for {$this->patientName($appointment)}'s appointment.");
    }

    /**
     * The patient picks their own new time. The appointment moves there and
     * goes back to Pending for the clinic to approve; any open proposal is dropped.
     */
    public function patientMove(Appointment $appointment, int $dentistId, string $date, string $time, array $serviceIds): Appointment
    {
        $this->assertReschedulable($appointment);
        $dentist = $this->assertDentist($dentistId);
        $services = $this->servicesFor($serviceIds);
        $slots = $this->slotsFor($services);
        $this->assertFuture($date, $time);

        $appointment = DB::transaction(function () use ($appointment, $dentist, $date, $time, $services, $slots) {
            $appointment = Appointment::with('patientInfo.userAccount')->lockForUpdate()->findOrFail($appointment->AppointmentID);
            $this->assertReschedulable($appointment);

            foreach (RescheduleRequest::where('AppointmentID', $appointment->AppointmentID)->where('Status', 'Pending')->lockForUpdate()->get() as $open) {
                $open->update(['Status' => 'Superseded', 'RespondedAt' => now()]);
                $this->release($open->ProposedDentistID, $open->ProposedDate->format('Y-m-d'), $open->held_times);
            }

            $oldDentist = $appointment->DentistID;
            $oldDate = $appointment->AppointmentDate->format('Y-m-d');
            $oldTimes = $this->timesOf($appointment);

            $times = $this->claim($dentist->UserID, $date, $time, $slots, $appointment->AppointmentID);
            $this->moveAppointment($appointment, $dentist->UserID, $date, $times[0], $slots, $services->pluck('ServiceID')->all());
            $appointment->Status = 'Pending';
            $appointment->ApprovedAt = null;
            $appointment->save();

            $this->release($oldDentist, $oldDate, $oldTimes, $appointment->AppointmentID);

            return $appointment;
        });

        $this->notifications->notifyPatient(
            $appointment,
            'Reschedule Requested',
            "Your appointment was moved to {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label} with {$dentist->display_name}. It's now waiting for the clinic's approval — we'll email you once it's reviewed.",
            'warning',
            'Pending'
        );

        $this->notifications->notifyAdmins(
            'Appointment Rescheduled by Patient',
            "{$this->patientName($appointment)} moved their appointment to {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label} with {$dentist->display_name} — it's waiting for approval.",
            'info',
            $appointment->AppointmentID,
            'Pending'
        );
        $this->activityLog->log('Appointment Rescheduled', "Moved the appointment to {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label} with {$dentist->display_name} (waiting for approval).");

        return $appointment;
    }

    /** The appointment is no longer active — drop any open proposal and free its hold. */
    public function closeForAppointment(Appointment $appointment): void
    {
        foreach (RescheduleRequest::where('AppointmentID', $appointment->AppointmentID)->where('Status', 'Pending')->get() as $open) {
            $open->update(['Status' => 'Withdrawn', 'RespondedAt' => now()]);
            $this->release($open->ProposedDentistID, $open->ProposedDate->format('Y-m-d'), $open->held_times);
        }
    }

    /** Close proposals nobody answered in time and free their held slots. */
    public function expireStale(): int
    {
        $stale = RescheduleRequest::with('appointment.patientInfo.userAccount')->where('Status', 'Pending')->where('ExpiresAt', '<=', now())->get();

        foreach ($stale as $proposal) {
            DB::transaction(function () use ($proposal) {
                $proposal->update(['Status' => 'Expired', 'RespondedAt' => now()]);
                $this->release($proposal->ProposedDentistID, $proposal->ProposedDate->format('Y-m-d'), $proposal->held_times);
            });

            $appointment = $proposal->appointment;
            if (!$appointment) {
                continue;
            }
            $this->notifications->notifyPatient(
                $appointment,
                'Proposed Time Expired',
                "The proposed new time ({$proposal->when_label}) expired without a response, so your appointment stays on {$appointment->AppointmentDate->format('F j, Y')}, {$appointment->time_range_label}.",
                'info'
            );
            $this->notifications->notifyAdmins(
                'Reschedule Expired',
                "{$this->patientName($appointment)} didn't answer the proposed time ({$proposal->when_label}) — the original time stays and the held slot was released.",
                'warning',
                $appointment->AppointmentID,
                $appointment->Status
            );
        }

        return $stale->count();
    }

    // ------------------------------------------------------------------
    // Slot helpers
    // ------------------------------------------------------------------

    /**
     * Lock and reserve $slots consecutive slots from $time, refusing if any
     * is closed, booked, or held by another proposal. Returns the times.
     */
    protected function claim(int $dentistId, string $date, string $time, int $slots, int $forAppointmentId): array
    {
        $all = DentistSchedule::slotTimes();
        $start = array_search($time, $all, true);
        if ($start === false) {
            throw new RuntimeException('That time is not a valid appointment slot.');
        }
        $times = array_slice($all, $start, $slots);
        if (count($times) < $slots) {
            throw new RuntimeException('That visit would run past closing time. Please pick an earlier start time.');
        }
        if (\App\Models\ClosedDate::isClosed($date) || \App\Models\ClosedDate::isClosedWeekday($date)) {
            throw new RuntimeException('The clinic is closed on that day. Please pick another date.');
        }

        $this->assertNoOtherHolders($dentistId, $date, $times, $forAppointmentId, null);

        foreach ($times as $t) {
            $row = DentistSchedule::where('DentistID', $dentistId)->where('Date', $date)->where('Time', $t)->lockForUpdate()->first()
                ?? DentistSchedule::create(['DentistID' => $dentistId, 'Date' => $date, 'Time' => $t, 'Status' => 'Available']);
            if ($row->Status !== 'Available') {
                throw new RuntimeException(Carbon::createFromFormat('H:i', $t)->format('g:i A') . ' on that day is no longer available. Please pick another time.');
            }
            $row->Status = 'Not Available';
            $row->save();
        }

        return $times;
    }

    /** Refuse if any other appointment or open proposal holds one of $times. */
    protected function assertNoOtherHolders(int $dentistId, string $date, array $times, ?int $exceptAppointmentId, ?int $exceptRescheduleId): void
    {
        $held = $this->heldTimes($dentistId, $date, $exceptAppointmentId, $exceptRescheduleId);
        foreach ($times as $t) {
            if (in_array($t, $held, true)) {
                throw new RuntimeException(Carbon::createFromFormat('H:i', $t)->format('g:i A') . ' on that day is already taken. Please pick another time.');
            }
        }
    }

    /** Times held by Pending/Approved appointments and open proposals (minus the given ones). */
    protected function heldTimes(int $dentistId, string $date, ?int $exceptAppointmentId = null, ?int $exceptRescheduleId = null): array
    {
        $held = [];
        Appointment::where('DentistID', $dentistId)->whereDate('AppointmentDate', $date)
            ->whereIn('Status', ['Pending', 'Approved'])
            ->when($exceptAppointmentId, fn ($q) => $q->whereKeyNot($exceptAppointmentId))
            ->get()
            ->each(function ($a) use (&$held) { $held = array_merge($held, $this->timesOf($a)); });

        RescheduleRequest::where('ProposedDentistID', $dentistId)->whereDate('ProposedDate', $date)->where('Status', 'Pending')
            ->when($exceptRescheduleId, fn ($q) => $q->whereKeyNot($exceptRescheduleId))
            ->get()
            ->each(function ($r) use (&$held) { $held = array_merge($held, $r->held_times); });

        return array_values(array_unique($held));
    }

    /** Free slots — but never one still held by an appointment or another open proposal. */
    protected function release(int $dentistId, string $date, array $times, ?int $exceptAppointmentId = null): void
    {
        $stillHeld = $this->heldTimes($dentistId, $date, $exceptAppointmentId);
        $free = array_values(array_diff($times, $stillHeld));
        if ($free) {
            DentistSchedule::where('DentistID', $dentistId)->where('Date', $date)->whereIn('Time', $free)->update(['Status' => 'Available']);
        }
    }

    protected function timesOf(Appointment $appointment): array
    {
        $all = DentistSchedule::slotTimes();
        $start = array_search($appointment->AppointmentTime, $all, true);

        return $start === false ? [] : array_slice($all, $start, $appointment->duration_slots);
    }

    protected function moveAppointment(Appointment $appointment, int $dentistId, string $date, string $time, int $slots, array $serviceIds): void
    {
        $services = Service::whereIn('ServiceID', $serviceIds)->get();
        $firstRow = DentistSchedule::where('DentistID', $dentistId)->where('Date', $date)->where('Time', $time)->first();

        $appointment->DentistID = $dentistId;
        $appointment->AppointmentDate = $date;
        $appointment->AppointmentTime = $time;
        $appointment->ScheduleID = $firstRow?->ScheduleID ?? $appointment->ScheduleID;
        $appointment->DurationHours = round($slots * DentistSchedule::SLOT_MINUTES / 60, 1);
        if ($services->isNotEmpty()) {
            $appointment->ServiceID = $services->first()->ServiceID;
            $appointment->TypeOfAppointment = $services->pluck('ServiceName')->implode(', ');
            $appointment->services()->sync($services->pluck('ServiceID'));
        }
        // Reminders already sent were for the OLD time; un-mark them (the
        // notifications stay visible) so the new time gets its own reminders.
        \App\Models\Notification::where('AppointmentID', $appointment->AppointmentID)
            ->whereNotNull('ReminderType')
            ->update(['ReminderType' => null]);
    }

    // ------------------------------------------------------------------
    // Guards
    // ------------------------------------------------------------------

    public function assertReschedulable(Appointment $appointment): void
    {
        if (!$appointment->can_reschedule) {
            throw new RuntimeException('This appointment can no longer be rescheduled — it has already started, or its status changed.');
        }
    }

    protected function assertOpen(RescheduleRequest $proposal): void
    {
        if ($proposal->Status !== 'Pending') {
            throw new RuntimeException('This proposed time is no longer open — it may have expired or been withdrawn.');
        }
        if ($proposal->ExpiresAt->lte(now())) {
            throw new RuntimeException('This proposed time has expired.');
        }
    }

    protected function assertDentist(int $dentistId): UserAccount
    {
        $dentist = UserAccount::dentists()->where('UserID', $dentistId)->first();
        if (!$dentist) {
            throw new RuntimeException('Please choose a dentist.');
        }

        return $dentist;
    }

    protected function assertFuture(string $date, string $time): void
    {
        if (Carbon::parse($date . ' ' . $time)->lte(now())) {
            throw new RuntimeException('That time has already passed. Please pick an upcoming time.');
        }
    }

    protected function servicesFor(array $serviceIds)
    {
        $services = Service::whereIn('ServiceID', $serviceIds)->where('IsArchived', false)->get();
        if ($services->isEmpty()) {
            throw new RuntimeException('Please choose at least one service.');
        }
        if ($services->count() > Service::MAX_PER_APPOINTMENT) {
            throw new RuntimeException('You can choose up to ' . Service::MAX_PER_APPOINTMENT . ' services per appointment.');
        }

        return $services;
    }

    protected function slotsFor($services): int
    {
        $minutes = $services->sum(fn ($s) => $s->DurationMinutes ?? 60);

        return max(1, (int) ceil($minutes / DentistSchedule::SLOT_MINUTES));
    }

    protected function patientName(?Appointment $appointment): string
    {
        $p = $appointment?->patientInfo;

        return $p ? trim($p->FirstName . ' ' . $p->LastName) : 'A patient';
    }
}
