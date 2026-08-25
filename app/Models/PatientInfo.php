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
}