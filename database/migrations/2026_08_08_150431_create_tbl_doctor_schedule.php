<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tbl_dentistSchedule', function (Blueprint $table) {
            $table->id('ScheduleID');
            $table->date('Date');
            $table->string('Time', 5); // stored as 'HH:MM' e.g. '09:00', '13:00'
            $table->enum('Status', ['Available', 'Not Available'])->default('Available');
            $table->timestamps();

            // one row per date+time slot
            $table->unique(['Date', 'Time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_dentistSchedule');
    }
};
