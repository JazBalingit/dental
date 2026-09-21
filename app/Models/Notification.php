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

    /**
     * Notifications about a booked / approved / completed appointment open
     * the appointment's details when clicked. Everything else (reminders,
     * cancellations, declines, failures, non-appointment notices) stays a
     * plain notice. Judged by the status stamped on the notification when it
     * was sent, not the appointment's current status.
     */
    public const DETAIL_STATUSES = ['Pending', 'Approved', 'Completed'];

    public function opensAppointmentDetails(): bool
    {
        return $this->AppointmentID !== null
            && $this->ReminderType === null
            && in_array($this->Status, self::DETAIL_STATUSES, true);
    }

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'AppointmentID', 'AppointmentID');
    }
}
