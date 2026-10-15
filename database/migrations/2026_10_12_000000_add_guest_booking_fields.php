<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Booking without an account + booking for someone else.
 *
 *  tbl_patientInfo   GuardianEmail / GuardianUserID — the person who books
 *                    for this patient (a parent booking their child), by
 *                    email for guests and by account once they sign up.
 *                    Relationship — how the patient relates to them.
 *  tbl_appointments  who booked it (account or verified email + name),
 *                    whether it was for someone else, and the secret token
 *                    behind the "Manage my appointment" email link.
 *  tbl_notifications UserID becomes nullable: a guest has no account, but
 *                    the emails sent to them are still logged here (which
 *                    also keeps reminders from going out twice).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->string('GuardianEmail', 255)->nullable()->after('ParentsEmail')->index();
            $table->unsignedBigInteger('GuardianUserID')->nullable()->after('GuardianEmail')->index();
            $table->string('Relationship', 50)->nullable()->after('GuardianUserID');
        });

        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('BookedByUserID')->nullable()->after('Source')->index();
            $table->string('BookedByEmail', 255)->nullable()->after('BookedByUserID')->index();
            $table->string('BookedByName', 150)->nullable()->after('BookedByEmail');
            $table->string('BookedByPhone', 20)->nullable()->after('BookedByName');
            $table->boolean('BookedForOther')->default(false)->after('BookedByPhone');
            $table->string('ManageToken', 64)->nullable()->unique()->after('BookedForOther');
        });

        DB::statement('ALTER TABLE tbl_notifications MODIFY UserID BIGINT UNSIGNED NULL');
        Schema::table('tbl_notifications', function (Blueprint $table) {
            $table->string('Email', 255)->nullable()->after('UserID');
        });

        // Existing portal bookings were all made by the patient themselves.
        DB::statement("UPDATE tbl_appointments a JOIN tbl_patientInfo p ON p.PatientID = a.PatientID
            SET a.BookedByUserID = p.UserID WHERE a.Source = 'Online' AND p.UserID IS NOT NULL AND a.BookedByUserID IS NULL");
    }

    public function down(): void
    {
        Schema::table('tbl_notifications', function (Blueprint $table) {
            $table->dropColumn('Email');
        });
        DB::statement('DELETE FROM tbl_notifications WHERE UserID IS NULL');
        DB::statement('ALTER TABLE tbl_notifications MODIFY UserID BIGINT UNSIGNED NOT NULL');

        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->dropUnique(['ManageToken']);
            $table->dropIndex(['BookedByUserID']);
            $table->dropIndex(['BookedByEmail']);
            $table->dropColumn(['BookedByUserID', 'BookedByEmail', 'BookedByName', 'BookedByPhone', 'BookedForOther', 'ManageToken']);
        });

        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->dropIndex(['GuardianEmail']);
            $table->dropIndex(['GuardianUserID']);
            $table->dropColumn(['GuardianEmail', 'GuardianUserID', 'Relationship']);
        });
    }
};
