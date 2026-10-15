<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * A staff-proposed new time for an appointment. While Pending, the
     * appointment keeps its original slot AND the proposed slot is held
     * (schedule rows Not Available) so nobody else can take it. The patient
     * accepts (appointment moves), keeps the original (proposal dropped), or
     * picks their own time. Unanswered proposals expire.
     */
    public function up(): void
    {
        Schema::create('tbl_reschedule_requests', function (Blueprint $table) {
            $table->id('RescheduleID');
            $table->unsignedBigInteger('AppointmentID');
            $table->unsignedBigInteger('ProposedDentistID');
            $table->date('ProposedDate');
            $table->string('ProposedTime', 5);
            $table->unsignedTinyInteger('DurationSlots');
            $table->string('ServiceIDs', 100)->nullable(); // CSV of ServiceID
            $table->string('Reason', 500)->nullable();
            $table->enum('Status', ['Pending', 'Accepted', 'Declined', 'Expired', 'Withdrawn', 'Superseded'])->default('Pending');
            $table->unsignedBigInteger('ProposedBy')->nullable();
            $table->dateTime('ExpiresAt');
            $table->dateTime('RespondedAt')->nullable();
            $table->timestamps();

            $table->index(['AppointmentID', 'Status'], 'resched_appt_status_idx');
            $table->index(['ProposedDentistID', 'ProposedDate', 'Status'], 'resched_dentist_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_reschedule_requests');
    }
};
