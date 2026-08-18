<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_patientRecord', function (Blueprint $table) {
            $table->id('RecordID');
            $table->unsignedBigInteger('PatientID');
            $table->unsignedBigInteger('AppointmentID')->nullable();
            // No tbl_walkIns table exists yet anywhere in this app (the walk-in
            // page is still a static mockup) — kept as a plain nullable column
            // matching the ERD's shape, without a FK to a table that isn't built.
            $table->unsignedBigInteger('WalkInID')->nullable();
            $table->unsignedBigInteger('ServiceID')->nullable();

            $table->date('VisitDate');
            $table->string('VisitTime', 5); // 'HH:MM'
            $table->string('Service')->nullable(); // snapshot of the service name at visit time
            $table->string('Status')->default('Completed');
            $table->text('Notes')->nullable();
            $table->boolean('IsArchived')->default(false);

            $table->timestamps();

            $table->foreign('PatientID')->references('PatientID')->on('tbl_patientInfo')->onDelete('cascade');
            $table->foreign('AppointmentID')->references('AppointmentID')->on('tbl_appointments')->nullOnDelete();
            $table->foreign('ServiceID')->references('ServiceID')->on('tbl_services')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_patientRecord');
    }
};
