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
     * Any appointment still Pending once its scheduled date/time has passed
     * was never reviewed in time — decline it automatically, release its
     * held slot, and let the patient know so they can book again instead of
     * waiting on a request nobody will act on anymore. No audit log entry
     * is written for this — it's a system action, not a staff one, and
     * tbl_auditLogs.StaffID is a required foreign key to a real account.
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
            $appointment->Status = 'Declined';
            $appointment->DeclineReason = 'This appointment request expired before it could be reviewed.';
            $appointment->save();

            DentistSchedule::where('ScheduleID', $appointment->ScheduleID)->update(['Status' => 'Available']);

            $user = $appointment->patientInfo->userAccount ?? null;

            if ($user) {
                $this->notifications->notifyUser(
                    $user,
                    'Appointment Request Expired',
                    "We're sorry — your appointment request wasn't reviewed in time and has expired. Please feel free to book another appointment at your convenience.",
                    'danger',
                    $appointment->AppointmentID,
                    'Declined'
                );
            }
        }

        return $stale->count();
    }
}
