<?php
// Place in: database/migrations/
// Must run AFTER tbl_patientRecord, tbl_patientInfo and tbl_appointments.
//
// One row per charted tooth per patient-record (i.e. per completed visit).
// Anchoring on RecordID — not just PatientID — is what preserves the
// odontogram history: every visit keeps its own snapshot of tooth
// conditions instead of a single chart being overwritten each visit.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_odontogram_teeth', function (Blueprint $table) {
            $table->id('OdontogramToothID');

            $table->unsignedBigInteger('RecordID');
            $table->unsignedBigInteger('PatientID');
            $table->unsignedBigInteger('AppointmentID')->nullable();

            $table->string('ToothNumber', 2);          // FDI notation, 11–48
            $table->string('Condition', 20);           // healthy | caries | filling | root_canal | crown | missing
            $table->string('Surfaces')->nullable();    // csv: mesial,distal,occlusal,buccal,lingual,incisal
            $table->text('Description')->nullable();

            $table->timestamps();

            // A tooth can only carry one condition entry within a single visit.
            $table->unique(['RecordID', 'ToothNumber']);

            $table->foreign('RecordID')->references('RecordID')->on('tbl_patientRecord')->onDelete('cascade');
            $table->foreign('PatientID')->references('PatientID')->on('tbl_patientInfo')->onDelete('cascade');
            $table->foreign('AppointmentID')->references('AppointmentID')->on('tbl_appointments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_odontogram_teeth');
    }
};
