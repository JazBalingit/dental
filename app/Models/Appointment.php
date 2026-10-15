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
        'PatientNotes',
        'MedicalHistoryID',
        'Source',
        'BookedByUserID',
        'BookedByEmail',
        'BookedByName',
        'BookedByPhone',
        'BookedForOther',
        'ManageToken',
        'Status',
        'DurationHours',
        'DeclineReason',
        'ApprovedAt',
    ];

    protected $casts = [
        'AppointmentDate' => 'date:Y-m-d',
        'ApprovedAt' => 'datetime',
        'DurationHours' => 'float',
        'BookedForOther' => 'boolean',
    ];

    /**
     * Admin-list ordering: Pending first (earliest scheduled visit on top,
     * since those are the ones waiting on a decision), then Approved,
     * Completed, Cancelled, Declined. Every non-pending group is newest-first
     * by when it last changed — ApprovedAt for approved rows, updated_at
     * (the moment it was completed/cancelled/declined) for the rest.
     */
    /**
     * A patient's live appointment: Pending/Approved AND dated today or
     * later. A past Approved visit that staff never marked Completed is
     * left alone for staff to close out — but it mustn't count as
     * "upcoming" or stop the patient from booking again.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereIn('Status', ['Pending', 'Approved'])
            ->whereDate('AppointmentDate', '>=', today());
    }

    public function scopeWorkflowOrder($query)
    {
        return $query
            ->orderByRaw("FIELD(Status, 'Pending', 'Approved', 'Completed', 'No Show', 'Cancelled', 'Declined')")
            ->orderByRaw("CASE WHEN Status = 'Pending' THEN AppointmentDate END ASC")
            ->orderByRaw("CASE WHEN Status = 'Pending' THEN AppointmentTime END ASC")
            ->orderByRaw("CASE WHEN Status = 'Approved' THEN COALESCE(ApprovedAt, updated_at) ELSE updated_at END DESC");
    }

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

    /** Photos the patient attached when booking (metadata only — bytes aren't selected). */
    public function attachments()
    {
        return $this->hasMany(AppointmentAttachment::class, 'AppointmentID', 'AppointmentID')
            ->select(['AttachmentID', 'AppointmentID', 'FileName', 'MimeType', 'SizeBytes', 'created_at']);
    }

    /**
     * However an appointment stops being active (cancelled, declined,
     * completed, no-show — from any screen), close its open reschedule
     * proposal and free the slot that proposal was holding.
     */
    protected static function booted(): void
    {
        static::saved(function (Appointment $appointment) {
            if ($appointment->wasChanged('Status') && !in_array($appointment->Status, ['Pending', 'Approved'], true)) {
                app(\App\Services\RescheduleService::class)->closeForAppointment($appointment);
            }
        });
    }

    /** The medical history version confirmed for this visit. */
    public function medicalHistory()
    {
        return $this->belongsTo(MedicalHistory::class, 'MedicalHistoryID', 'MedicalHistoryID');
    }

    /** The account that made this booking (the patient, or a parent booking for a child). */
    public function bookedBy()
    {
        return $this->belongsTo(UserAccount::class, 'BookedByUserID', 'UserID');
    }

    /** Booked online without an account — managed by email and the manage link. */
    public function getIsGuestAttribute(): bool
    {
        return $this->Source === 'Online' && !$this->BookedByUserID && $this->BookedByEmail
            && !($this->patientInfo?->UserID);
    }

    /** Who booked an appointment made for someone else ("Maria Santos"), else null. */
    public function getBookedByLabelAttribute(): ?string
    {
        if (!$this->BookedForOther) {
            return null;
        }

        return $this->BookedByName ?: ($this->bookedBy?->patientInfo?->full_name ?: $this->BookedByEmail);
    }

    /**
     * May this signed-in account manage (view, cancel, reschedule) this
     * appointment? Their own, one they booked for someone else, or one for
     * a patient they're the guardian of.
     */
    public function isManagedBy(?UserAccount $user): bool
    {
        if (!$user) {
            return false;
        }

        $patient = $this->patientInfo;

        return ($patient && $patient->UserID === $user->UserID)
            || $this->BookedByUserID === $user->UserID
            || ($patient && $patient->GuardianUserID === $user->UserID);
    }

    /** The secret behind this appointment's "Manage my appointment" link — created on first use. */
    public function ensureManageToken(): string
    {
        if ($this->ManageToken) {
            return $this->ManageToken;
        }

        // Another copy of this appointment (e.g. the one the email was built
        // from) may already have made one — reuse it so emailed links stay valid.
        $existing = static::whereKey($this->getKey())->value('ManageToken');
        if ($existing) {
            return $this->ManageToken = $existing;
        }

        $token = bin2hex(random_bytes(24));
        // Only set it if still empty, so two racing copies can't overwrite each other.
        static::whereKey($this->getKey())->whereNull('ManageToken')->update(['ManageToken' => $token]);

        return $this->ManageToken = static::whereKey($this->getKey())->value('ManageToken') ?? $token;
    }

    public function getManageUrlAttribute(): string
    {
        return route('manage.show', $this->ensureManageToken());
    }

    /** The staff reschedule proposal still waiting for the patient, if any. */
    public function pendingReschedule()
    {
        return $this->hasOne(RescheduleRequest::class, 'AppointmentID', 'AppointmentID')
            ->where('Status', 'Pending')
            ->latestOfMany('RescheduleID');
    }

    /** Can staff (or the patient) still move this appointment? Pending/Approved and not started yet. */
    public function getCanRescheduleAttribute(): bool
    {
        return in_array($this->Status, ['Pending', 'Approved'], true)
            && \Carbon\Carbon::parse($this->AppointmentDate->format('Y-m-d') . ' ' . $this->AppointmentTime)->isFuture();
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