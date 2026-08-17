<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_useraccount', function (Blueprint $table) {
            // 'User' = patient (or the super admin), 'Staff' = clinic staff/dentist —
            // distinguishes which *Info table (tbl_patientInfo vs tbl_staffInfo) to join.
            $table->string('AccountType')->default('User')->after('AccountRole');
            $table->string('Position')->nullable()->after('AccountType'); // 'Dentist' | 'Staff', only set when AccountType = 'Staff'
            $table->boolean('IsArchived')->default(false)->after('Position');
            $table->timestamp('EmailVerifiedAt')->nullable()->after('IsArchived');
            $table->timestamp('LastLoginAt')->nullable()->after('EmailVerifiedAt');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_useraccount', function (Blueprint $table) {
            $table->dropColumn(['AccountType', 'Position', 'IsArchived', 'EmailVerifiedAt', 'LastLoginAt']);
        });
    }
};
