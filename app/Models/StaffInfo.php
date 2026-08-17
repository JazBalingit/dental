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
    ];

    // Usage in Blade: $staffInfo->photo_url
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
