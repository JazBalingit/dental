<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffInfo extends Model
{
    protected $table = 'tbl_staffInfo';
    protected $primaryKey = 'StaffInfoID';
    public $timestamps = false;

    protected $fillable = [
        'UserID',
        'LastName',
        'FirstName',
        'MiddleName',
        'PhoneNumber',
        'DateOfBirth',
        'Age',
        'Gender',
        'Religion',
        'Nationality',
        'Address',
        'ProfilePicture',
    ];

    protected $casts = [
        'DateOfBirth' => 'date:Y-m-d',
        // "EDERLYN SALE" → "Ederlyn Sale" (see App\Casts\PersonName).
        'FirstName' => \App\Casts\PersonName::class,
        'MiddleName' => \App\Casts\PersonName::class,
        'LastName' => \App\Casts\PersonName::class,
    ];

    /** Age in years — from the birthdate when known, else the stored Age. */
    public function getAgeYearsAttribute(): ?int
    {
        return $this->DateOfBirth ? $this->DateOfBirth->age : ($this->Age !== null ? (int) $this->Age : null);
    }

    // Usage in Blade: $staffInfo->photo_url
    public function getPhotoUrlAttribute()
    {
        // Stored in the database (survives redeploys); the default avatar when
        // there's no photo, or an old file upload has since been wiped.
        return ProfilePhoto::urlFor($this->ProfilePicture) ?? asset('images/default.png');
    }

    public function userAccount()
    {
        return $this->belongsTo(UserAccount::class, 'UserID', 'UserID');
    }
}
