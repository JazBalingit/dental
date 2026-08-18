<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('AppointmentID')->nullable()->after('UserID');
            $table->string('Status')->nullable()->after('Type'); // snapshot of appointment status at notification time
            $table->string('ReminderType')->nullable()->after('Status'); // 'day_before' | 'hour_before' | 'on_time'

            $table->foreign('AppointmentID')->references('AppointmentID')->on('tbl_appointments')->nullOnDelete();
            $table->unique(['AppointmentID', 'ReminderType'], 'notif_appt_reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_notifications', function (Blueprint $table) {
            $table->dropUnique('notif_appt_reminder_unique');
            $table->dropForeign(['AppointmentID']);
            $table->dropColumn(['AppointmentID', 'Status', 'ReminderType']);
        });
    }
};
