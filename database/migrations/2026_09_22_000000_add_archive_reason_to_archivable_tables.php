<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every archivable thing keeps the reason it was archived (typed in the
 * archive dialog) and when, on the record itself — not only in the Activity
 * Log. Cleared again on unarchive.
 *
 * Table names use the exact casing the models / earlier migrations use, since
 * MySQL on Linux is case-sensitive about them.
 */
return new class extends Migration {
    private array $tables = [
        'tbl_useraccount',
        'tbl_patientRecord',
        'tbl_services',
        'tbl_service_categories',
        'tbl_appointment_steps',
        'tbl_activityLogs',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'ArchiveReason')) {
                    $t->text('ArchiveReason')->nullable();
                }
                if (!Schema::hasColumn($table, 'ArchivedAt')) {
                    $t->timestamp('ArchivedAt')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                foreach (['ArchiveReason', 'ArchivedAt'] as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $t->dropColumn($column);
                    }
                }
            });
        }
    }
};
