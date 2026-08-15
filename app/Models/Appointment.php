<?php
// Place in: app/Models/Appointment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'tbl_appointments';
    protected $primaryKey = 'AppointmentID';

    protected $fillable = [
        'PatientID',
        'ScheduleID',
        'ServiceID',
        'AppointmentDate',
        'AppointmentTime',
        'TypeOfAppointment',
        'Status',
        'DurationHours',
        'DeclineReason',
    ];

    protected $casts = [
        'AppointmentDate' => 'date:Y-m-d',
    ];

    public function patientInfo()
    {
        return $this->belongsTo(PatientInfo::class, 'PatientID', 'PatientID');
    }

    public function schedule()
    {
        return $this->belongsTo(DentistSchedule::class, 'ScheduleID', 'ScheduleID');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'ServiceID', 'ServiceID');
    }
}