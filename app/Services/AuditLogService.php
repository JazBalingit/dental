<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    /**
     * Records a staff/admin action. The acting staff member is always the
     * currently logged-in session user — every caller of this method sits
     * behind an admin-only guard() check already.
     */
    public function log(string $actionType, string $description): AuditLog
    {
        return AuditLog::create([
            'StaffID' => session('user_id'),
            'ActionType' => $actionType,
            'Description' => $description,
        ]);
    }
}
