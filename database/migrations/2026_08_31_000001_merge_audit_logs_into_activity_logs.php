<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * "Audit logs" and "activity logs" are now one thing. Move every
 * tbl_auditLogs row into tbl_activityLogs, then drop the audit table.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('tbl_auditLogs')) {
            DB::statement("
                INSERT INTO tbl_activityLogs (UserID, ActivityType, Description, IsArchived, created_at, updated_at)
                SELECT a.StaffID,
                       a.ActionType,
                       a.Description,
                       a.IsArchived,
                       a.created_at,
                       a.updated_at
                FROM tbl_auditLogs a
            ");

            // Snapshot the actor name for the rows we just imported.
            DB::statement("
                UPDATE tbl_activityLogs al
                LEFT JOIN tbl_useraccount ua ON ua.UserID = al.UserID
                LEFT JOIN tbl_staffinfo si ON si.UserID = ua.UserID
                LEFT JOIN tbl_patientinfo pi ON pi.UserID = ua.UserID
                SET al.ActorName = COALESCE(
                    NULLIF(TRIM(CONCAT(COALESCE(si.FirstName, pi.FirstName, ''), ' ', COALESCE(si.LastName, pi.LastName, ''))), ''),
                    ua.Email
                )
                WHERE al.ActorName IS NULL AND al.LoggedInTime IS NULL
            ");

            Schema::drop('tbl_auditLogs');
        }
    }

    public function down(): void
    {
        Schema::create('tbl_auditLogs', function (Blueprint $table) {
            $table->id('AuditLogID');
            $table->unsignedBigInteger('StaffID');
            $table->string('ActionType');
            $table->text('Description')->nullable();
            $table->boolean('IsArchived')->default(false);
            $table->timestamps();

            $table->foreign('StaffID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
        });
    }
};
