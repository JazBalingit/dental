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
        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->string('ProfilePicture')->nullable()->after('Occupation');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_patientInfo', function (Blueprint $table) {
            $table->dropColumn('ProfilePicture');
        });
    }
};
