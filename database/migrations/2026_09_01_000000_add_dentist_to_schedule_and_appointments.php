<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Schedules and appointments become per-dentist. Every staff account with
 * Position = 'Dentist' now keeps its own availability grid, patients pick a
 * dentist when booking, and each appointment records who it's with.
 *
 * Existing rows (one shared grid + its appointments) are attributed to the
 * first dentist on file so nothing is orphaned.
 */
return new class extends Migration {
    public function up(): void
    {
        // Prefer an active dentist (IsArchived = 0 sorts first) so migrated
        // rows stay visible in the filters.
        $firstDentistId = DB::table('tbl_useraccount')
            ->where('AccountType', 'Staff')
            ->where('Position', 'Dentist')
            ->orderBy('IsArchived')
            ->orderBy('UserID')
            ->value('UserID');

        Schema::table('tbl_dentistschedule', function (Blueprint $table) {
            $table->unsignedBigInteger('DentistID')->nullable()->after('ScheduleID');
        });

        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('DentistID')->nullable()->after('PatientID');
        });

        if ($firstDentistId) {
            DB::table('tbl_dentistschedule')->update(['DentistID' => $firstDentistId]);
            DB::table('tbl_appointments')->update(['DentistID' => $firstDentistId]);
        }

        // The old "one slot per Date+Time" uniqueness becomes "one slot per
        // Dentist+Date+Time" — each dentist has an independent grid.
        Schema::table('tbl_dentistschedule', function (Blueprint $table) {
            $table->dropUnique('tbl_dentistschedule_date_time_unique');
        });

        Schema::table('tbl_dentistschedule', function (Blueprint $table) {
            $table->unique(['DentistID', 'Date', 'Time'], 'schedule_dentist_date_time_unique');
            $table->foreign('DentistID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
        });

        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->foreign('DentistID')->references('UserID')->on('tbl_useraccount')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->dropForeign(['DentistID']);
            $table->dropColumn('DentistID');
        });

        Schema::table('tbl_dentistschedule', function (Blueprint $table) {
            $table->dropForeign(['DentistID']);
            $table->dropUnique('schedule_dentist_date_time_unique');
        });

        // Old data can have duplicate Date+Time rows once DentistID is gone;
        // keep the lowest ScheduleID per Date+Time before restoring the unique.
        DB::statement('
            DELETE s1 FROM tbl_dentistschedule s1
            INNER JOIN tbl_dentistschedule s2
            WHERE s1.Date = s2.Date AND s1.Time = s2.Time AND s1.ScheduleID > s2.ScheduleID
        ');

        Schema::table('tbl_dentistschedule', function (Blueprint $table) {
            $table->dropColumn('DentistID');
            $table->unique(['Date', 'Time'], 'tbl_dentistschedule_date_time_unique');
        });
    }
};
