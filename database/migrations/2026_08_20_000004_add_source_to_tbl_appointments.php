<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->enum('Source', ['Online', 'Walk-in'])->default('Online')->after('TypeOfAppointment');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_appointments', function (Blueprint $table) {
            $table->dropColumn('Source');
        });
    }
};
