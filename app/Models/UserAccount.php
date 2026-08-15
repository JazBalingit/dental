<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    //
    protected $table = 'tbl_useraccount';
    protected $primaryKey = 'UserID';
    public $timestamps = false; // uses DateCreated instead of created_at/updated_at

    protected $fillable = [
        'Email',
        'Password',
        'AccountRole',
        'DateCreated',
        'FirstName',
        'LastName',
        'PhoneNumber',
        'StaffRole',
        'IsArchived',
    ];

    protected $hidden = [
        'Password',
    ];

    public function patientInfo()
    {
        return $this->hasOne(PatientInfo::class, 'UserID', 'UserID');
    }
}
