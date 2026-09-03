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
     * "Dr. Jane Cruz" — the dentist this slot belongs to, or a plain
     * fallback for rows with no dentist recorded. Mirrors
     * Appointment::dentist_name so reports read the same either way.
     */
    public function getDentistNameAttribute(): string
    {
        return $this->dentist?->display_name ?? 'Unassigned';
    }

    /**
     * The clinic day, as a slot grid — single source of truth for "what
     * times can an appointment start at". Every controller that reasons
     * about appointment slots (booking, approval, cancellation, dashboard
     * stats, the admin schedule grid) reads this instead of keeping its
     * own copy, so they can never drift out of sync with each other.
     *
     * The open/close/lunch window is editable by the super admin in
     * Configuration → System Information; the constants below are only the
     * fallback used when nothing has been saved yet.
     */
    public const SLOT_MINUTES = 30;
    public const OPEN_TIME = '09:00';
    public const CLOSE_TIME = '18:00';
    public const LUNCH_START = '12:00';
    public const LUNCH_END = '13:00';

    /** Per-request memo so slotTimes() isn't re-querying settings in a loop. */
    protected static ?array $clinicHoursCache = null;

    /**
     * The saved (or default) clinic booking window. Keys: open, close,
     * lunchEnabled, lunchStart, lunchEnd — all times as "H:i".
     */
    public static function clinicHours(): array
    {
        if (static::$clinicHoursCache !== null) {
            return static::$clinicHoursCache;
        }

        $time = function (string $key, string $fallback): string {
            $value = SystemSetting::get($key, $fallback);
            try {
                return Carbon::createFromFormat('H:i', substr((string) $value, 0, 5))->format('H:i');
            } catch (\Exception $e) {
                return $fallback;
            }
        };

        return static::$clinicHoursCache = [
            'open' => $time('booking_open_time', static::OPEN_TIME),
            'close' => $time('booking_close_time', static::CLOSE_TIME),
            'lunchEnabled' => (string) SystemSetting::get('booking_lunch_enabled', '1') === '1',
            'lunchStart' => $time('booking_lunch_start', static::LUNCH_START),
            'lunchEnd' => $time('booking_lunch_end', static::LUNCH_END),
        ];
    }

    /** Drop the memo — call after saving new clinic hours in the same request. */
    public static function flushClinicHoursCache(): void
    {
        static::$clinicHoursCache = null;
    }

    /**
     * "9:00 AM – 6:00 PM" — the public display string for the saved booking
     * window, shown on the landing page.
     */
    public static function clinicHoursLabel(): string
    {
        $hours = static::clinicHours();

        return Carbon::createFromFormat('H:i', $hours['open'])->format('g:i A')
            . ' – ' . Carbon::createFromFormat('H:i', $hours['close'])->format('g:i A');
    }

    /**
     * Every bookable start time in order — half-hour slots across the saved
     * open→close window, skipping the lunch break when one is enabled.
     */
    public static function slotTimes(): array
    {
        $hours = static::clinicHours();

        $times = [];
        $cursor = Carbon::createFromFormat('H:i', $hours['open']);
        $close = Carbon::createFromFormat('H:i', $hours['close']);
        $lunchStart = Carbon::createFromFormat('H:i', $hours['lunchStart']);
        $lunchEnd = Carbon::createFromFormat('H:i', $hours['lunchEnd']);
        $hasLunch = $hours['lunchEnabled'] && $lunchStart->lt($lunchEnd);

        // Guard against a misconfigured window (close at/before open).
        if ($close->lte($cursor)) {
            return [];
        }

        while ($cursor->lt($close)) {
            $inLunch = $hasLunch && $cursor->gte($lunchStart) && $cursor->lt($lunchEnd);
            if (!$inLunch) {
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