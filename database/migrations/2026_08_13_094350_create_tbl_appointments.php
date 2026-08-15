<?php
// Place in: database/migrations/
// Must run AFTER tbl_patientInfo, tbl_doctorSchedule, and tbl_services migrations

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_appointments', function (Blueprint $table) {
            $table->id('AppointmentID');

            $table->unsignedBigInteger('PatientID');
            $table->unsignedBigInteger('ScheduleID');
            $table->unsignedBigInteger('ServiceID');

            $table->date('AppointmentDate');
            $table->string('AppointmentTime', 5); // 'HH:MM', matches tbl_doctorSchedule.Time format
            $table->string('TypeOfAppointment')->nullable();
            $table->enum('Status', ['Pending', 'Approved', 'Declined', 'Completed'])->default('Pending');

            // Not on the ERD screenshot, but needed to implement "approve with a
            // duration that blocks the following slots" — flagged clearly here
            // in case your ERD needs updating for the defense.
            $table->unsignedTinyInteger('DurationHours')->nullable();
            $table->text('DeclineReason')->nullable();

            $table->timestamps();

            $table->foreign('PatientID')->references('PatientID')->on('tbl_patientInfo')->onDelete('cascade');
            $table->foreign('ScheduleID')->references('ScheduleID')->on('tbl_dentistSchedule')->onDelete('cascade');
            $table->foreign('ServiceID')->references('ServiceID')->on('tbl_services')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_appointments');
    }
};  