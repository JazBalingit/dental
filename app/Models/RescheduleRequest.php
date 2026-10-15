<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * A staff-proposed new time for an appointment, waiting for the patient.
 * See App\Services\RescheduleService for the whole lifecycle.
 */
class RescheduleRequest extends Model
{
    protected $table = 'tbl_reschedule_requests';
    protected $primaryKey = 'RescheduleID';

    /** How long a patient has to answer, at most (it also ends at the original visit's start). */
    public const RESPONSE_HOURS = 24;

    protected $fillable = [
        'AppointmentID',
        'ProposedDentistID',
        'ProposedDate',
        'ProposedTime',
        'DurationSlots',
        'ServiceIDs',
        'Reason',
        'Status',
        'ProposedBy',
        'ExpiresAt',
        'RespondedAt',
    ];

    protected $casts = [
        'ProposedDate' => 'date:Y-m-d',
        'ExpiresAt' => 'datetime',
        'RespondedAt' => 'datetime',
        'DurationSlots' => 'integer',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'AppointmentID', 'AppointmentID');
    }

    public function dentist()
    {
        return $this->belongsTo(UserAccount::class, 'ProposedDentistID', 'UserID');
    }

    public function proposer()
    {
        return $this->belongsTo(UserAccount::class, 'ProposedBy', 'UserID');
    }

    /** @return int[] */
    public function getServiceIdListAttribute(): array
    {
        return array_values(array_filter(array_map('intval', explode(',', (string) $this->ServiceIDs))));
    }

    /** The slot times this proposal holds, e.g. ['09:00','09:30',…]. */
    public function getHeldTimesAttribute(): array
    {
        $times = DentistSchedule::slotTimes();
        $start = array_search($this->ProposedTime, $times, true);

        return $start === false ? [] : array_slice($times, $start, $this->DurationSlots);
    }

    /** "October 20, 2026 · 9:00 AM – 10:30 AM" */
    public function getWhenLabelAttribute(): string
    {
        $held = $this->held_times;
        $start = Carbon::createFromFormat('H:i', $this->ProposedTime)->format('g:i A');
        $end = $held
            ? Carbon::createFromFormat('H:i', end($held))->addMinutes(DentistSchedule::SLOT_MINUTES)->format('g:i A')
            : $start;

        return $this->ProposedDate->format('F j, Y') . ' · ' . $start . ' – ' . $end;
    }
}
