<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

/**
 * Automated reminders for Approved appointments.
 *
 * Four stages, each with the number of minutes before the appointment at
 * which it becomes due:
 *
 *   day_before  24 h   "…scheduled in 1 day"
 *   hour_before  1 h   "…scheduled in 1 hour"
 *   coming_now  20 min "You may now proceed to the clinic…"
 *   on_time      0     "…scheduled for this time"
 *
 * The rule that matters: a patient only ever gets the reminder for the stage
 * they are CURRENTLY in — the latest one whose time has passed. Stages that
 * were already behind us when the appointment got approved are never sent
 * late. So an appointment approved 14 hours out gets a "14 hours" reminder
 * (not a day-before one, then an hour-before one, then …), one approved 21
 * minutes out gets a single "21 minutes" reminder, and one approved after it
 * started gets a single "your appointment is now".
 *
 * The wording always states the REAL time remaining when the reminder goes
 * out, never the stage's nominal one.
 */
class AppointmentReminderService
{
    /** stage => minutes before the appointment it becomes due (in order). */
    public const STAGES = [
        'day_before' => 1440,
        'hour_before' => 60,
        'coming_now' => 20,
        'on_time' => 0,
    ];

    /**
     * If a reminder already went out within this many minutes BEFORE the next
     * stage falls due (e.g. approved at T-21 min, so the 20-minute stage is
     * only a minute away), that next stage would just repeat it — skip it.
     */
    public const NEAR_DUPLICATE_MINUTES = 3;

    /** An "it's now" reminder is only still useful this long after the start. */
    public const ON_TIME_GRACE_MINUTES = 60;

    public function __construct(protected NotificationService $notifications)
    {
    }

    /** @return int number of reminders sent */
    public function sendDue(?Carbon $now = null): int
    {
        $now = ($now ?? Carbon::now())->copy();
        $sent = 0;

        // Only appointments starting within the next 24 h (or that just began)
        // can have a stage due, so don't scan the whole table every minute.
        $appointments = Appointment::with(['patientInfo.userAccount', 'service', 'dentist.staffInfo'])
            ->where('Status', 'Approved')
            ->whereDate('AppointmentDate', '>=', $now->copy()->subDay()->toDateString())
            ->whereDate('AppointmentDate', '<=', $now->copy()->addDay()->toDateString())
            ->get();

        foreach ($appointments as $appointment) {
            $user = $appointment->patientInfo->userAccount ?? null;

            if (!$user) {
                continue; // walk-ins / patients without an account
            }

            $start = $this->startsAt($appointment, $now);

            if (!$start || $now->gt($start->copy()->addMinutes(self::ON_TIME_GRACE_MINUTES))) {
                continue;
            }

            $stage = $this->currentStage($start, $now);

            if ($stage === null) {
                continue; // more than a day away — nothing due yet
            }

            $earlier = Notification::where('AppointmentID', $appointment->AppointmentID)
                ->whereNotNull('ReminderType')
                ->get();

            if ($earlier->contains('ReminderType', $stage)) {
                continue; // this stage was already sent
            }

            $due = $start->copy()->subMinutes(self::STAGES[$stage]);
            $last = $earlier->max('created_at');

            if ($last && Carbon::parse($last)->gte($due->copy()->subMinutes(self::NEAR_DUPLICATE_MINUTES))) {
                continue; // a reminder just went out — this one would only repeat it
            }

            try {
                $this->notifications->notifyUser(
                    $user,
                    'Appointment Reminder',
                    $this->message($stage, $start, $now),
                    'info',
                    $appointment->AppointmentID,
                    $appointment->Status,
                    $stage
                );
                $sent++;
            } catch (QueryException) {
                // The unique (appointment, reminder type) index caught a race
                // with a concurrent run — it was already sent.
            }
        }

        return $sent;
    }

    /** The latest stage whose time has passed, or null if none has yet. */
    public function currentStage(Carbon $start, Carbon $now): ?string
    {
        $current = null;

        foreach (self::STAGES as $stage => $minutesBefore) {
            if ($now->gte($start->copy()->subMinutes($minutesBefore))) {
                $current = $stage;
            }
        }

        return $current;
    }

    /** The reminder text for a stage, worded from the real time left. */
    public function message(string $stage, Carbon $start, Carbon $now): string
    {
        $when = $start->format('l, F j, Y') . ' at ' . $start->format('g:i A');
        $left = static::timeLeft($start, $now);

        return match ($stage) {
            'coming_now' => "You may now proceed to the clinic. Your appointment is scheduled in {$left}, on {$when}.",
            'on_time' => "Your appointment is scheduled for this time ({$when}). You may now proceed to the clinic.",
            default => "This is a courtesy reminder that your appointment is scheduled in {$left}, on {$when}.",
        };
    }

    /**
     * "1 day" (about a day out), "14 hours", "1 hour", "21 minutes", "1 minute".
     * Minutes are rounded UP, so a reminder that fires a few seconds after its
     * threshold still reads "1 hour" / "20 minutes" rather than "59 minutes".
     */
    public static function timeLeft(Carbon $start, Carbon $now): string
    {
        $minutes = max(1, (int) ceil(($start->getTimestamp() - $now->getTimestamp()) / 60));

        if ($minutes >= 1430) { // within ~10 min of a full day
            return '1 day';
        }

        if ($minutes >= 60) {
            $hours = intdiv($minutes, 60);

            return $hours . ' ' . ($hours === 1 ? 'hour' : 'hours');
        }

        return $minutes . ' ' . ($minutes === 1 ? 'minute' : 'minutes');
    }

    protected function startsAt(Appointment $appointment, Carbon $now): ?Carbon
    {
        try {
            return Carbon::createFromFormat(
                'Y-m-d H:i',
                $appointment->AppointmentDate->format('Y-m-d') . ' ' . $appointment->AppointmentTime,
                $now->getTimezone()
            );
        } catch (\Throwable) {
            return null;
        }
    }
}
