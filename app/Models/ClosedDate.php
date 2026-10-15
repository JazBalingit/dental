<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Clinic-wide closures (holidays, etc.) — an active (IsArchived=false) row
 * blocks booking for every dentist on that Date, everywhere the booking
 * calendar is used. Separate from DentistSchedule's per-dentist,
 * per-slot Not Available rows.
 */
class ClosedDate extends Model
{
    protected $table = 'tbl_closed_dates';
    protected $primaryKey = 'ClosedDateID';

    protected $fillable = [
        'Date',
        'Reason',
        'ClosedBy',
        'IsArchived',
        'ArchiveReason',
        'ArchivedAt',
    ];

    protected $casts = [
        'Date' => 'date',
        'IsArchived' => 'boolean',
        'ArchivedAt' => 'datetime',
    ];

    public function closedBy()
    {
        return $this->belongsTo(UserAccount::class, 'ClosedBy', 'UserID');
    }

    /** date('Y-m-d') => Reason, for every currently-active closure. Memoized per request. */
    protected static ?array $activeCache = null;

    protected static function activeMap(): array
    {
        if (static::$activeCache !== null) {
            return static::$activeCache;
        }

        return static::$activeCache = static::where('IsArchived', false)
            ->get(['Date', 'Reason'])
            ->mapWithKeys(fn ($row) => [$row->Date->format('Y-m-d') => $row->Reason])
            ->all();
    }

    public static function isClosed(Carbon|string $date): bool
    {
        $key = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');

        return array_key_exists($key, static::activeMap());
    }

    /** The reason the date is closed, or null if it isn't closed / has no reason on file. */
    public static function reasonFor(Carbon|string $date): ?string
    {
        $key = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');

        return static::activeMap()[$key] ?? null;
    }

    public static function flushCache(): void
    {
        static::$activeCache = null;
        static::$weekdayCache = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Weekly closed days — e.g. "closed every Sunday". Stored as a CSV of
    | Carbon dayOfWeek numbers (0 = Sunday … 6 = Saturday) in the
    | 'closed_weekdays' system setting; defaults to Sunday only, which is
    | what the clinic hard-coded before this was configurable.
    |--------------------------------------------------------------------------
    */

    /** Carbon dayOfWeek => name, in Monday-first display order. */
    public const WEEKDAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        0 => 'Sunday',
    ];

    protected static ?array $weekdayCache = null;

    /** @return int[] the closed dayOfWeek numbers. Memoized per request. */
    public static function closedWeekdays(): array
    {
        if (static::$weekdayCache !== null) {
            return static::$weekdayCache;
        }

        $raw = (string) SystemSetting::get('closed_weekdays', '0');

        return static::$weekdayCache = collect(explode(',', $raw))
            ->map(fn ($d) => trim($d))
            ->filter(fn ($d) => $d !== '' && array_key_exists((int) $d, static::WEEKDAYS))
            ->map(fn ($d) => (int) $d)
            ->unique()
            ->values()
            ->all();
    }

    /** True when $date falls on one of the clinic's weekly closed days. */
    public static function isClosedWeekday(Carbon|string $date): bool
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return in_array($date->dayOfWeek, static::closedWeekdays(), true);
    }

    /** "Sundays", "Saturdays and Sundays", … — '' when every day is open. */
    public static function closedWeekdaysLabel(): string
    {
        $names = collect(static::WEEKDAYS)
            ->filter(fn ($name, $dow) => in_array($dow, static::closedWeekdays(), true))
            ->map(fn ($name) => $name . 's')
            ->values();

        if ($names->count() <= 1) {
            return (string) $names->first();
        }

        return $names->slice(0, -1)->implode(', ') . ' and ' . $names->last();
    }
}
