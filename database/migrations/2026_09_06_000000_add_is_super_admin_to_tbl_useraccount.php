<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Marks the one real super-admin account. The account otherwise looks
     * like any other admin (AccountRole = 'admin') so every check that keys
     * on user_role === 'admin' keeps working; super-admin power is gated on
     * this flag + the session('is_super_admin') flag set at login.
     */
    public function up(): void
    {
        Schema::table('tbl_useraccount', function (Blueprint $table) {
            $table->boolean('IsSuperAdmin')->default(false)->after('AccountType');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_useraccount', function (Blueprint $table) {
            $table->dropColumn('IsSuperAdmin');
        });
    }
};
