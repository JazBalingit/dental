<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A photo for accounts with no StaffInfo / PatientInfo row — the claimed
 * super admin — so every account can set one. Holds "photo:{PhotoID}".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('tbl_useraccount', 'ProfilePicture')) {
            Schema::table('tbl_useraccount', function (Blueprint $table) {
                $table->string('ProfilePicture')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tbl_useraccount', 'ProfilePicture')) {
            Schema::table('tbl_useraccount', function (Blueprint $table) {
                $table->dropColumn('ProfilePicture');
            });
        }
    }
};
