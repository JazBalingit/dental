<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentStep extends Model
{
    protected $table = 'tbl_appointment_steps';
    protected $primaryKey = 'StepID';

    protected $fillable = [
        'Title',
        'Description',
        'DisplayOrder',
        'IsArchived',
    ];

    protected $casts = [
        'DisplayOrder' => 'integer',
        'IsArchived' => 'boolean',
    ];
}
