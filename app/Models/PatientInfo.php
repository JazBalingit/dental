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
        return $this->ProfilePicture
            ? asset($this->ProfilePicture)
            : asset('images/default.png');
    }

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'PatientID', 'PatientID');
    }

    /**
     * A patient is "inactive" once 6 months pass with no appointment at all
     * (any status counts — booking activity is what signals they're still
     * engaged with the clinic). A patient who has never booked yet stays
     * "Active" — there's no "last" appointment to have gone stale.
     *
     * Reads the withMax('appointments', 'AppointmentDate') aggregate when
     * the caller preloaded it (avoids an extra query per row in a list),
     * falling back to a direct query otherwise.
     */
    public function getIsInactiveAttribute(): bool
    {
        $lastDate = array_key_exists('appointments_max_appointmentdate', $this->attributes)
            ? $this->attributes['appointments_max_appointmentdate']
            : $this->appointments()->max('AppointmentDate');

        return $lastDate !== null && \Carbon\Carbon::parse($lastDate)->lt(now()->subMonths(6));
    }
}