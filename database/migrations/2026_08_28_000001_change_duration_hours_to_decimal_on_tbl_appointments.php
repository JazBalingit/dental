<?php
// Place in: database/migrations/
// The booking calendar moved from 1-hour to 30-minute slots, so an
// appointment's duration can now be a half-hour (e.g. 1.5 hours) — the old
// integer column can't hold that. Existing whole-hour values (1, 2, 5, ...)
// remain valid under the new decimal type, so no data migration is needed.
// Uses raw SQL instead of Schema::table(...)->change() to avoid requiring
// doctrine/dbal just for this one column-type change.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE tbl_appointments MODIFY DurationHours DECIMAL(4,1) UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tbl_appointments MODIFY DurationHours TINYINT UNSIGNED NULL');
    }
};
