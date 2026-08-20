<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'tbl_activityLogs';
    protected $primaryKey = 'ActivityLogsID';
    public $timestamps = false;

    protected $fillable = [
        'UserID',
        'ActivityType',
        'LoggedInTime',
        'LoggedOutTime',
        'IsArchived',
    ];

    protected $casts = [
        'LoggedInTime' => 'datetime',
        'LoggedOutTime' => 'datetime',
        'IsArchived' => 'boolean',
    ];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }
}
