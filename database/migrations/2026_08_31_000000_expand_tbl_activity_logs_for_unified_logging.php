<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns tbl_activityLogs into the single system-wide activity trail. It
 * used to record only patient/staff login + logout sessions; it now also
 * carries what tbl_auditLogs held (admin/staff actions) plus failed login
 * and failed password-change attempts.
 *
 *  - Description  : human-readable line for non-session events
 *  - ActorName    : who did it, snapshotted so the row still reads right
 *                   even for a failed login with an unknown email
 *  - IpAddress    : request IP, useful for failed-login rows
 *  - UserID       : now nullable — a failed login for an email that isn't
 *                   an account has no UserID to point at
 *  - created_at   : the event time for non-session rows (session rows keep
 *                   using LoggedInTime)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_activityLogs', function (Blueprint $table) {
            $table->text('Description')->nullable()->after('ActivityType');
            $table->string('ActorName')->nullable()->after('Description');
            $table->string('IpAddress', 45)->nullable()->after('ActorName');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        // Drop the NOT NULL constraint on UserID. The foreign key already
        // cascades on delete; keeping it, just allowing null.
        Schema::table('tbl_activityLogs', function (Blueprint $table) {
            $table->dropForeign(['UserID']);
        });

        DB::statement('ALTER TABLE tbl_activityLogs MODIFY UserID BIGINT UNSIGNED NULL');

        Schema::table('tbl_activityLogs', function (Blueprint $table) {
            $table->foreign('UserID')->references('UserID')->on('tbl_useraccount')->nullOnDelete();
        });

        // Backfill created_at for the existing login/logout rows so ordering
        // by created_at doesn't push them all to the bottom.
        DB::statement('UPDATE tbl_activityLogs SET created_at = COALESCE(LoggedInTime, LoggedOutTime), updated_at = COALESCE(LoggedOutTime, LoggedInTime) WHERE created_at IS NULL');
    }

    public function down(): void
    {
        Schema::table('tbl_activityLogs', function (Blueprint $table) {
            $table->dropForeign(['UserID']);
        });

        DB::statement('DELETE FROM tbl_activityLogs WHERE UserID IS NULL');
        DB::statement('ALTER TABLE tbl_activityLogs MODIFY UserID BIGINT UNSIGNED NOT NULL');

        Schema::table('tbl_activityLogs', function (Blueprint $table) {
            $table->foreign('UserID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
            $table->dropColumn(['Description', 'ActorName', 'IpAddress', 'created_at', 'updated_at']);
        });
    }
};
