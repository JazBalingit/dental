<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 'No Show' — the patient never arrived for an Approved appointment.
        // Raw ALTER for the same reason as the 'Cancelled' migration (no doctrine/dbal).
        DB::statement("ALTER TABLE tbl_appointments MODIFY Status ENUM('Pending','Approved','Declined','Completed','Cancelled','No Show') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tbl_appointments SET Status = 'Cancelled' WHERE Status = 'No Show'");
        DB::statement("ALTER TABLE tbl_appointments MODIFY Status ENUM('Pending','Approved','Declined','Completed','Cancelled') NOT NULL DEFAULT 'Pending'");
    }
};
