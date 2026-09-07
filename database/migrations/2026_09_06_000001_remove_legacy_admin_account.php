<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Drops the seeded demo admin (admin@gmail.com / admin123). Real admin
     * accounts are created in Staff Accounts now, so the default is just an
     * open door. FKs: tbl_activitylogs.UserID is ON DELETE SET NULL,
     * tbl_notifications is ON DELETE CASCADE — the row has no appointments,
     * staffinfo, or dentist schedules.
     */
    public function up(): void
    {
        DB::table('tbl_useraccount')->where('Email', 'admin@gmail.com')->delete();
    }

    public function down(): void
    {
        // The original bcrypt hash can't be restored — nothing to roll back.
    }
};
