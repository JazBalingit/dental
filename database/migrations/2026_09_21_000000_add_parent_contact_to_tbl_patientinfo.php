<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A minor's own phone/email are not collected — the parent/guardian's
    // contact details live here instead.
    public function up(): void
    {
        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->string('ParentsContactNumber', 20)->nullable()->after('ParentsOccupation');
            $table->string('ParentsEmail')->nullable()->after('ParentsContactNumber');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->dropColumn(['ParentsContactNumber', 'ParentsEmail']);
        });
    }
};