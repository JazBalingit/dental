<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_useraccount', function (Blueprint $table) {
            $table->string('FirstName')->nullable()->after('AccountRole');
            $table->string('LastName')->nullable()->after('FirstName');
            $table->string('PhoneNumber', 20)->nullable()->after('LastName');
            $table->string('StaffRole')->nullable()->after('PhoneNumber');
            $table->boolean('IsArchived')->default(false)->after('StaffRole');
            $table->timestamp('EmailVerifiedAt')->nullable()->after('IsArchived');
            $table->timestamp('LastLoginAt')->nullable()->after('EmailVerifiedAt');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_useraccount', function (Blueprint $table) {
            $table->dropColumn(['FirstName', 'LastName', 'PhoneNumber', 'StaffRole', 'IsArchived', 'EmailVerifiedAt', 'LastLoginAt']);
        });
    }
};
