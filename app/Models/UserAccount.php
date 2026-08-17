<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    protected $table = 'tbl_useraccount';
    protected $primaryKey = 'UserID';
    public $timestamps = false; // uses DateCreated instead of created_at/updated_at

    protected $fillable = [
        'Email',
        'Password',
        'AccountRole',
        'AccountType',
        'Position',
        'DateCreated',
        'IsArchived',
        'EmailVerifiedAt',
        'LastLoginAt',
    ];

    protected $casts = [
        'EmailVerifiedAt' => 'datetime',
        'LastLoginAt' => 'datetime',
        'IsArchived' => 'boolean',
    ];

    protected $hidden = [
        'Password',
    ];

    public function patientInfo()
    {
        return $this->hasOne(PatientInfo::class, 'UserID', 'UserID');
    }

    public function staffInfo()
    {
        return $this->hasOne(StaffInfo::class, 'UserID', 'UserID');
    }
}
