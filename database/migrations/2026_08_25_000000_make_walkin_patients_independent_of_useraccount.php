<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Walk-in patients recorded by staff no longer get a placeholder row in
 * tbl_useraccount (that polluted the accounts table and blocked the
 * patient's real email from ever being used to sign up). UserID becomes
 * optional so a walk-in's PatientInfo can stand on its own, IsWalkIn marks
 * rows created this way, and Email lets staff record the walk-in's contact
 * email without an account.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->dropForeign(['UserID']);
        });

        DB::statement('ALTER TABLE tbl_patientInfo MODIFY UserID BIGINT UNSIGNED NULL');

        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->foreign('UserID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
            $table->boolean('IsWalkIn')->default(false)->after('UserID');
            $table->string('Email')->nullable()->after('PhoneNumber');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->dropForeign(['UserID']);
            $table->dropColumn(['IsWalkIn', 'Email']);
        });

        DB::statement('ALTER TABLE tbl_patientInfo MODIFY UserID BIGINT UNSIGNED NOT NULL');

        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->foreign('UserID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
        });
    }
};
