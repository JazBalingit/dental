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
        'Title',
        'Message',
        'Type',
        'IsRead',
    ];

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }
}