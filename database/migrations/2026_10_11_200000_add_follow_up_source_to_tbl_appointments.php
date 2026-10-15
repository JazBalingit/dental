<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Follow-ups booked by staff get their own Source so reports can tell
    // them apart from patient self-bookings (Online) and Walk-ins.
    public function up(): void
    {
        DB::statement("ALTER TABLE tbl_appointments MODIFY Source ENUM('Online','Walk-in','Follow-up') NOT NULL DEFAULT 'Online'");
    }

    public function down(): void
    {
        DB::statement("UPDATE tbl_appointments SET Source = 'Walk-in' WHERE Source = 'Follow-up'");
        DB::statement("ALTER TABLE tbl_appointments MODIFY Source ENUM('Online','Walk-in') NOT NULL DEFAULT 'Online'");
    }
};
