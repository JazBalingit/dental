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
    }
}
