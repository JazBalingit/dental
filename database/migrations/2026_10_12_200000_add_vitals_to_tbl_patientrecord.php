<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blood pressure and bleeding time are measured at each visit, so they live
 * on the visit's record (not in the medical history, which only holds what
 * stays true between visits — like blood type).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_patientRecord', function (Blueprint $table) {
            $table->string('BloodPressure', 20)->nullable()->after('Notes');
            $table->string('BleedingTime', 30)->nullable()->after('BloodPressure');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_patientRecord', function (Blueprint $table) {
            $table->dropColumn(['BloodPressure', 'BleedingTime']);
        });
    }
};
