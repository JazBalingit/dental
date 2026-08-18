<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Laravel's fluent enum change() needs doctrine/dbal, which isn't
        // installed here — raw ALTER is the standard workaround.
        DB::statement("ALTER TABLE tbl_appointments MODIFY Status ENUM('Pending','Approved','Declined','Completed','Cancelled') NOT NULL DEFAULT 'Pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE tbl_appointments MODIFY Status ENUM('Pending','Approved','Declined','Completed') NOT NULL DEFAULT 'Pending'");
    }
};
