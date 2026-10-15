<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A patient's medical history — the "Medical History" part of the clinic's
 * paper Patient Information Record. Every update is a NEW row (never an
 * overwrite): the latest row is the patient's current history, and each
 * appointment points at the version that was confirmed for that visit, so
 * the dentist can always see what was declared at the time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_medical_histories', function (Blueprint $table) {
            $table->id('MedicalHistoryID');
            $table->unsignedBigInteger('PatientID')->index();
            $table->json('Answers');
            // Who filled it in: the patient / the person who booked for them, or clinic staff.
            $table->string('Source', 20)->default('patient');
            $table->unsignedBigInteger('UpdatedByUserID')->nullable();
            $table->string('UpdatedByName', 150)->nullable();
            $table->timestamps();

            $table->foreign('PatientID')->references('PatientID')->on('tbl_patientInfo')->onDelete('cascade');
        });

        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('MedicalHistoryID')->nullable()->after('PatientNotes');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->dropColumn('MedicalHistoryID');
        });
        Schema::dropIfExists('tbl_medical_histories');
    }
};
