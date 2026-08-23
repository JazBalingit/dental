<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    /**
     * Records a staff/admin action. The acting staff member is always the
     * currently logged-in session user — every caller of this method sits
     * behind an admin-only guard() check already.
     *
     * The super admin bypass login (see LoginController) has no real
     * tbl_useraccount row, and StaffID is a foreign key into that table,
     * so its actions are skipped rather than written.
     */
    public function log(string $actionType, string $description): AuditLog
    {
        if (session('is_super_admin')) {
            return new AuditLog([
                'StaffID' => session('user_id'),
                'ActionType' => $actionType,
                'Description' => $description,
            ]);
        }

        return AuditLog::create([
            'StaffID' => session('user_id'),
            'ActionType' => $actionType,
            'Description' => $description,
        ]);
    }
}
