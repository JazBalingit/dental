<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Notification;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send day-before, hour-before, and on-time reminders for approved appointments';

    /**
     * Threshold offsets from the appointment datetime, checked in order.
     * Using a "now has crossed the threshold" check (rather than an exact
     * time match) means this works correctly no matter how often the
     * scheduler actually runs.
     */
    protected array $stages = [
        'day_before' => -24, // hours before appointment
        'hour_before' => -1,
        'on_time' => 0,
    ];

    public function handle(NotificationService $notifications): int
    {
        $now = Carbon::now('Asia/Manila');
        $sent = 0;

        $appointments = Appointment::with(['patientInfo.userAccount'])
            ->where('Status', 'Approved')
            ->get();

        foreach ($appointments as $appointment) {
            $user = $appointment->patientInfo->userAccount ?? null;

            if (!$user) {
                continue;
            }

            try {
                $dt = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $appointment->AppointmentDate->format('Y-m-d') . ' ' . $appointment->AppointmentTime,
                    'Asia/Manila'
                );
            } catch (\Exception) {
                continue;
            }

            // Stop bothering with appointments so far in the past that even
            // the on-time reminder window has long since closed.
            if ($dt->lt($now->copy()->subDay())) {
                continue;
            }

            $timeLabel = $dt->format('g:i A');

            foreach ($this->stages as $type => $hoursOffset) {
                $threshold = $dt->copy()->addHours($hoursOffset);

                if ($now->lt($threshold)) {
                    continue; // hasn't crossed this threshold yet
                }

                if (Notification::where('AppointmentID', $appointment->AppointmentID)
                    ->where('ReminderType', $type)
                    ->exists()) {
                    continue; // already sent
                }

                $message = match ($type) {
                    'day_before' => "Your appointment is tomorrow at {$timeLabel}.",
                    'hour_before' => "Your appointment is in 1 hour at {$timeLabel}.",
                    'on_time' => 'Your appointment is now.',
                };

                try {
                    $notifications->notifyUser(
                        $user,
                        'Appointment Reminder',
                        $message,
                        'info',
                        $appointment->AppointmentID,
                        $appointment->Status,
                        $type
                    );
                    $sent++;
                } catch (QueryException) {
                    // Unique index caught a race — already sent by a concurrent run.
                }
            }
        }

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
