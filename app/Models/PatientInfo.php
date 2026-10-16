<?php
// Place in: app/Models/PatientInfo.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientInfo extends Model
{
    protected $table = 'tbl_patientInfo';
    protected $primaryKey = 'PatientID';

    protected $fillable = [
        'UserID',
        'IsWalkIn',
        'LastName',
        'FirstName',
        'MiddleName',
        'PhoneNumber',
        'Email',
        'DateOfBirth',
        'Nationality',
        'Address',
        'ParentsName',
        'ParentsOccupation',
        'ParentsContactNumber',
        'ParentsEmail',
        'GuardianEmail',
        'GuardianUserID',
        'Relationship',
        'Age',
        'Gender',
        'Religion',
        'Occupation',
        'ProfilePicture',
    ];

    protected $casts = [
        'DateOfBirth' => 'date:Y-m-d',
        'IsWalkIn' => 'boolean',
    ];

    // Usage in Blade: $patientInfo->photo_url
    public function getPhotoUrlAttribute()
    {
        // Fall back to the default avatar when the file is gone (e.g. an upload
        // wiped by a Railway redeploy), instead of rendering a broken image.
        return $this->ProfilePicture && is_file(public_path($this->ProfilePicture))
            ? asset($this->ProfilePicture)
            : asset('images/default.png');
    }

    /** Age in years — from the birthdate when known, else the stored Age. */
    public function getAgeYearsAttribute(): ?int
    {
        return $this->DateOfBirth ? $this->DateOfBirth->age : ($this->Age !== null ? (int) $this->Age : null);
    }

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }

    public function records()
    {
        return $this->hasMany(PatientRecord::class, 'PatientID', 'PatientID');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'PatientID', 'PatientID');
    }

    /**
     * The clinic's six-month policy (Chapter 1, Definition of Terms): a
     * patient is "inactive" when their LAST CLINIC VISIT — the latest
     * Completed appointment — is more than 6 months ago. Pending, cancelled,
     * declined or no-show bookings are not visits. A patient who has never
     * completed a visit yet stays "Active" (nothing has gone stale).
     *
     * Reads the withLastVisit() aggregate when the caller preloaded it (no
     * extra query per row in a list), falling back to a direct query.
     */
    public function getIsInactiveAttribute(): bool
    {
        $lastDate = array_key_exists('appointments_max_appointmentdate', $this->attributes)
            ? $this->attributes['appointments_max_appointmentdate']
            : $this->appointments()->where('Status', 'Completed')->max('AppointmentDate');

        return $lastDate !== null && \Carbon\Carbon::parse($lastDate)->lt(now()->subMonths(6));
    }

    /** Preload each patient's last clinic visit (latest Completed appointment) for is_inactive. */
    public function scopeWithLastVisit($query)
    {
        return $query->withMax(['appointments' => fn ($a) => $a->where('Status', 'Completed')], 'AppointmentDate');
    }

    /** Relationship choices when booking for someone else. */
    public const RELATIONSHIPS = ['Child', 'Parent', 'Spouse', 'Sibling', 'Grandparent', 'Relative', 'Friend', 'Other'];

    /** Minimum age to book for yourself or create an account. */
    public const ADULT_AGE = 18;

    /** Every version of this patient's medical history, newest first. */
    public function medicalHistories()
    {
        return $this->hasMany(MedicalHistory::class, 'PatientID', 'PatientID')->orderByDesc('MedicalHistoryID');
    }

    /** The current medical history (the newest version), if any. */
    public function latestMedicalHistory()
    {
        return $this->hasOne(MedicalHistory::class, 'PatientID', 'PatientID')->latestOfMany('MedicalHistoryID');
    }

    public function guardianAccount()
    {
        return $this->belongsTo(UserAccount::class, 'GuardianUserID', 'UserID');
    }

    public function getFullNameAttribute(): string
    {
        return trim(($this->FirstName ?? '') . ' ' . ($this->LastName ?? ''));
    }

    /** "Registered" (has an account), "Walk-in", or "Guest" (booked online without an account). */
    public function getPatientTypeAttribute(): string
    {
        if ($this->UserID) {
            return 'Registered';
        }

        return $this->IsWalkIn ? 'Walk-in' : 'Guest';
    }

    /**
     * Everyone a verified email may book for or manage: the patient(s) whose
     * own email it is (guest or walk-in records without an account), plus
     * the people booked under it as their guardian. Never account holders —
     * those log in instead.
     */
    public function scopeUnderEmail($query, string $email)
    {
        $email = strtolower(trim($email));

        return $query->whereNull('UserID')->where(function ($q) use ($email) {
            $q->whereRaw('LOWER(Email) = ?', [$email])
                ->orWhereRaw('LOWER(GuardianEmail) = ?', [$email])
                ->orWhereRaw('LOWER(ParentsEmail) = ?', [$email]);
        });
    }

    /** True when this record is someone else booked under $email (not the email's owner). */
    public function isDependentOf(string $email): bool
    {
        return strcasecmp((string) $this->Email, $email) !== 0;
    }

    /** People a signed-in patient has booked for (their dependents). */
    public function scopeDependentsOf($query, UserAccount $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('GuardianUserID', $user->UserID)
                ->orWhere(fn ($q) => $q->whereNull('GuardianUserID')->whereRaw('LOWER(GuardianEmail) = ?', [strtolower($user->Email)]));
        })->where(fn ($q) => $q->whereNull('UserID')->orWhere('UserID', '!=', $user->UserID));
    }
}
