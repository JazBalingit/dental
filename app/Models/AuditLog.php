<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'tbl_auditLogs';
    protected $primaryKey = 'AuditLogID';

    protected $fillable = [
        'StaffID',
        'ActionType',
        'Description',
        'IsArchived',
    ];

    protected $casts = [
        'IsArchived' => 'boolean',
    ];

    public function staffAccount()
    {
        return $this->belongsTo(UserAccount::class, 'StaffID', 'UserID');
    }
}
