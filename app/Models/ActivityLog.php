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

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }
}
