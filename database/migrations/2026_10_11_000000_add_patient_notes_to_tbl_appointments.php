<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The patient's own note to the dentist when booking ("my lower left
     * molar hurts when I chew…") — shown with any attached photos on
     * Appointment Approval. Separate from DeclineReason and from the
     * dentist's visit notes (tbl_patientRecord.Notes).
     */
    public function up(): void
    {
        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->text('PatientNotes')->nullable()->after('TypeOfAppointment');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->dropColumn('PatientNotes');
        });
    }
};
