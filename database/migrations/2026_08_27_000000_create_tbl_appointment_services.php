<?php
// Place in: database/migrations/
// Lets one appointment cover several services. tbl_appointments.ServiceID is
// kept as the first selected service (existing relations/reports still work
// unchanged); this table holds the full set.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_appointment_services', function (Blueprint $table) {
            $table->id('AppointmentServiceID');

            $table->unsignedBigInteger('AppointmentID');
            $table->unsignedBigInteger('ServiceID');

            $table->timestamps();

            $table->foreign('AppointmentID')->references('AppointmentID')->on('tbl_appointments')->onDelete('cascade');
            $table->foreign('ServiceID')->references('ServiceID')->on('tbl_services')->onDelete('cascade');

            $table->unique(['AppointmentID', 'ServiceID']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_appointment_services');
    }
};
