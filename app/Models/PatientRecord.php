<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientRecord extends Model
{
    protected $table = 'tbl_patientRecord';
    protected $primaryKey = 'RecordID';

    protected $fillable = [
        'PatientID',
        'AppointmentID',
        'WalkInID',
        'ServiceID',
        'VisitDate',
        'VisitTime',
        'Service',
        'Status',
        'Notes',
        'IsArchived',
    ];

    protected $casts = [
        'VisitDate' => 'date:Y-m-d',
        'IsArchived' => 'boolean',
    ];

    public function patientInfo()
    {
        return $this->belongsTo(PatientInfo::class, 'PatientID', 'PatientID');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'AppointmentID', 'AppointmentID');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'ServiceID', 'ServiceID');
    }

    /**
     * Tooth conditions charted on this visit's odontogram. Scoped to the
     * record (not the patient) so each visit keeps its own history.
     */
    public function odontogramTeeth()
    {
        return $this->hasMany(OdontogramTooth::class, 'RecordID', 'RecordID');
    }
}
