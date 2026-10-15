<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * The single system-wide activity trail. One row can be either:
 *
 *  - a login session  — ActivityType 'Login', with LoggedInTime and
 *    (once the user logs out) LoggedOutTime
 *  - any other event   — ActivityType is the verb ('Logout', 'Failed Login',
 *    'Password Changed', 'Failed Password Change', 'Appointment Booked',
 *    'Appointment Cancelled', 'Appointment Rescheduled', 'Profile Updated',
 *    'Create', 'Edit', 'Archive', ...), with a human-readable Description
 *    and created_at as the event time
 */
class ActivityLog extends Model
{
    protected $table = 'tbl_activityLogs';
    protected $primaryKey = 'ActivityLogsID';

    protected $fillable = [
        'UserID',
        'ActivityType',
        'Description',
        'ActorName',
        'IpAddress',
        'LoggedInTime',
        'LoggedOutTime',
        'IsArchived',
        'ArchiveReason',
        'ArchivedAt',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'LoggedInTime' => 'datetime',
        'LoggedOutTime' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'IsArchived' => 'boolean',
    ];

    /**
     * Session rows predate created_at, so fall back to LoggedInTime.
     */
    public function getOccurredAtAttribute()
    {
        return $this->LoggedInTime ?? $this->created_at;
    }

    /**
     * A 'Login' row with no logout time yet is an open session.
     */
    public function getIsSessionAttribute(): bool
    {
        return $this->ActivityType === 'Login';
    }

    /**
     * Routine noise the trail no longer records (and hides if older rows
     * still exist): sign-ins/outs, failed attempts, contact-form messages.
     * The trail keeps only real changes — edits, creates, archives,
     * approvals, bookings, password/email changes.
     */
    public const IGNORED_TYPES = [
        'Login',
        'Logout',
        'Failed Login',
        'Failed Password Change',
        'Failed Booking',
        'Failed Super Admin Activation',
        'Failed Super Admin Release',
        'Contact Message',
    ];

    /** Only the meaningful entries — what every activity list shows. */
    public function scopeMeaningful($query)
    {
        return $query->whereNotIn('ActivityType', self::IGNORED_TYPES);
    }

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }
}
