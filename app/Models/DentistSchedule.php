<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DentistSchedule extends Model
{
    protected $table = 'tbl_dentistschedule';
    protected $primaryKey = 'ScheduleID';

    protected $fillable = [
        'DentistID',
        'Date',
        'Time',
        'Status',
    ];

    protected $casts = [
        'Date' => 'date:Y-m-d',
    ];

    public function dentist()
    {
        return $this->belongsTo(UserAccount::class, 'DentistID', 'UserID');
    }

    /**
     * The clinic day, as a slot grid — single source of truth for "what
     * times can an appointment start at". Every controller that reasons
     * about appointment slots (booking, approval, cancellation, dashboard
     * stats, the admin schedule grid) reads this instead of keeping its
     * own copy, so they can never drift out of sync with each other.
     */
    public const SLOT_MINUTES = 30;
    public const OPEN_TIME = '09:00';
    public const CLOSE_TIME = '18:00';
    public const LUNCH_START = '12:00';
    public const LUNCH_END = '13:00';

    /**
     * Every bookable start time in order — half-hour slots from 9:00 AM
     * to 6:00 PM, skipping the 12:00–1:00 PM lunch break.
     */
    public static function slotTimes(): array
    {
        $times = [];
        $cursor = Carbon::createFromFormat('H:i', static::OPEN_TIME);
        $close = Carbon::createFromFormat('H:i', static::CLOSE_TIME);
        $lunchStart = Carbon::createFromFormat('H:i', static::LUNCH_START);
        $lunchEnd = Carbon::createFromFormat('H:i', static::LUNCH_END);

        while ($cursor->lt($close)) {
            if ($cursor->lt($lunchStart) || $cursor->gte($lunchEnd)) {
                $times[] = $cursor->format('H:i');
            }
            $cursor->addMinutes(static::SLOT_MINUTES);
        }

        return $times;
    }

    /**
     * time => "9:00 AM" display label, for the booking calendar and admin
     * schedule grid. Kept to the start time only (not a "9:00 - 9:30"
     * range) — several places render this in a fixed-width column, and
     * every slot is a uniform 30 minutes so the range is implied.
     */
    public static function slotLabels(): array
    {
        $labels = [];

        foreach (static::slotTimes() as $time) {
            $labels[$time] = Carbon::createFromFormat('H:i', $time)->format('g:i A');
        }

        return $labels;
    }

    /**
     * "9:00 AM - 9:30 AM" — the fuller range label, for spots with room
     * to show it (e.g. a booking confirmation summary).
     */
    public static function slotRangeLabel(string $time): string
    {
        $start = Carbon::createFromFormat('H:i', $time);
        $end = $start->copy()->addMinutes(static::SLOT_MINUTES);

        return $start->format('g:i A') . ' - ' . $end->format('g:i A');
    }

    /**
     * "1 hour 30 minutes" / "30 minutes" / "2 hours" — for a given number
     * of half-hour slots. Shared by the booking failure/success messages
     * and Appointment::duration_label.
     */
    public static function formatSlotDuration(int $slots): string
    {
        $totalMinutes = max(1, $slots) * static::SLOT_MINUTES;
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;

        $parts = [];
        if ($hours > 0) {
            $parts[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
        }
        if ($minutes > 0) {
            $parts[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        }

        return $parts ? implode(' ', $parts) : '0 minutes';
    }
}