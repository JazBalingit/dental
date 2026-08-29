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
        'DentistID',
        'ScheduleID',
        'ServiceID',
        'AppointmentDate',
        'AppointmentTime',
        'TypeOfAppointment',
        'Source',
        'Status',
        'DurationHours',
        'DeclineReason',
        'ApprovedAt',
    ];

    protected $casts = [
        'AppointmentDate' => 'date:Y-m-d',
        'ApprovedAt' => 'datetime',
        'DurationHours' => 'float',
    ];

    public function patientInfo()
    {
        return $this->belongsTo(PatientInfo::class, 'PatientID', 'PatientID');
    }

    public function schedule()
    {
        return $this->belongsTo(DentistSchedule::class, 'ScheduleID', 'ScheduleID');
    }

    public function dentist()
    {
        return $this->belongsTo(UserAccount::class, 'DentistID', 'UserID');
    }

    /**
     * "Dr. Jane Cruz" — the assigned dentist's name, or a plain fallback
     * for legacy rows with no dentist recorded.
     */
    public function getDentistNameAttribute(): string
    {
        $info = $this->dentist?->staffInfo;

        if ($info) {
            return 'Dr. ' . trim($info->FirstName . ' ' . $info->LastName);
        }

        return $this->dentist?->Email ?? 'Unassigned';
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'ServiceID', 'ServiceID');
    }

    /**
     * Every service covered by this appointment. ServiceID/service() stay
     * pointed at the first one for backward compatibility with existing
     * relations and reports; this is the full set a patient booked.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'tbl_appointment_services', 'AppointmentID', 'ServiceID');
    }

    public function patientRecord()
    {
        return $this->hasOne(PatientRecord::class, 'AppointmentID', 'AppointmentID');
    }

    /**
     * How many half-hour schedule slots this appointment holds — the unit
     * every occupancy/release calculation works in. DurationHours is
     * stored in actual hours (e.g. 1.5), so this converts back to slots.
     */
    public function getDurationSlotsAttribute(): int
    {
        $hours = (float) ($this->DurationHours ?? (DentistSchedule::SLOT_MINUTES / 60));

        return max(1, (int) round(($hours * 60) / DentistSchedule::SLOT_MINUTES));
    }

    /**
     * "1 hour 30 minutes" / "30 minutes" — DurationHours formatted for
     * display, instead of every view appending "hour(s)" to a number that
     * might now be a fraction like 1.5.
     */
    public function getDurationLabelAttribute(): string
    {
        return DentistSchedule::formatSlotDuration($this->duration_slots);
    }

    /**
     * "9:00 AM - 1:30 PM" — the full span this appointment occupies, not
     * just its start time. Falls back to just the start time if
     * AppointmentTime isn't a recognized slot (legacy data).
     */
    public function getTimeRangeLabelAttribute(): string
    {
        $start = \Carbon\Carbon::createFromFormat('H:i', $this->AppointmentTime)->format('g:i A');

        $slotTimes = DentistSchedule::slotTimes();
        $startIndex = array_search($this->AppointmentTime, $slotTimes, true);

        if ($startIndex === false) {
            return $start;
        }

        $lastIndex = $startIndex + $this->duration_slots - 1;

        if (!isset($slotTimes[$lastIndex])) {
            return $start;
        }

        $end = \Carbon\Carbon::createFromFormat('H:i', $slotTimes[$lastIndex])
            ->addMinutes(DentistSchedule::SLOT_MINUTES)
            ->format('g:i A');

        return "{$start} - {$end}";
    }
}