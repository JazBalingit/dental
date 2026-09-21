<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\DentistSchedule;
use Carbon\Carbon;

class AppointmentExpiryService
{
    public function __construct(protected NotificationService $notifications)
    {
    }

    /**
     * Any appointment still Pending once its scheduled date/time has arrived
     * was never approved in time — cancel it automatically, release its held
     * slot, and let the patient know it wasn't approved so they can book
     * again instead of waiting on a request nobody will act on anymore. No
     * activity log entry is written for this — it's a system action, not one
     * taken by any signed-in user.
     */
    public function expireStalePending(): int
    {
        $stale = Appointment::with(['patientInfo.userAccount'])
            ->where('Status', 'Pending')
            ->get()
            ->filter(function (Appointment $appointment) {
                if (!$appointment->AppointmentDate || !$appointment->AppointmentTime) {
                    return false;
                }

                $scheduledFor = Carbon::parse($appointment->AppointmentDate->format('Y-m-d') . ' ' . $appointment->AppointmentTime);

                return $scheduledFor->lte(now());
            });

        foreach ($stale as $appointment) {
            $appointment->Status = 'Cancelled';
            $appointment->DeclineReason = 'This appointment was not approved before its scheduled time and was therefore automatically cancelled.';
            $appointment->save();

            $this->releaseSlotBlock($appointment);

            $user = $appointment->patientInfo->userAccount ?? null;

            if ($user) {
                $this->notifications->notifyUser(
                    $user,
                    'Appointment Cancelled',
                    'We regret to inform you that your appointment was not approved before its scheduled time and has therefore been automatically cancelled. You are welcome to book another appointment at your convenience.',
                    'danger',
                    $appointment->AppointmentID,
                    'Cancelled'
                );
            }
        }

        return $stale->count();
    }

    /**
     * Frees every slot this appointment was holding for its full
     * DurationHours block, not just its starting slot — same pattern as
     * AppointmentApprovalController::releaseSlotBlock and the other
     * cancel/decline paths, kept local rather than shared as elsewhere
     * in this app.
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
}
