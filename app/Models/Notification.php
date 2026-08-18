<?php
// Place in: app/Models/Notification.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'tbl_notifications';
    protected $primaryKey = 'NotificationID';

    protected $fillable = [
        'UserID',
        'AppointmentID',
        'Title',
        'Message',
        'Type',
        'Status',
        'ReminderType',
        'IsRead',
    ];

    protected $casts = [
        'IsRead' => 'boolean',
    ];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'AppointmentID', 'AppointmentID');
    }
}
