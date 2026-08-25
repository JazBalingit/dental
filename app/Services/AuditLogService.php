<?php

namespace App\Services;

use App\Models\AuditLog;

class AuditLogService
{
    /**
     * Records a staff/admin action. The acting staff member is always the
     * currently logged-in session user — every caller of this method sits
     * behind an admin-only guard() check already. This includes the super
     * admin login (see LoginController::loginAsSuperAdmin()), which is
     * backed by a real tbl_useraccount row precisely so its actions land
     * here like anyone else's.
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
