<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentistSchedule extends Model
{
    protected $table = 'tbl_dentistschedule';
    protected $primaryKey = 'ScheduleID';

    protected $fillable = [
        'Date',
        'Time',
        'Status',
    ];

    protected $casts = [
        'Date' => 'date:Y-m-d',
    ];
}