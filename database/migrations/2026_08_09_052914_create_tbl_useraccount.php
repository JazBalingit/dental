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
        Schema::create('tbl_useraccount', function (Blueprint $table) {
            $table->id('UserID');
            $table->string('Email')->unique();
            $table->string('Password');
            $table->string('AccountRole')->default('patient'); // 'admin' | 'staff' | 'patient'
            $table->timestamp('DateCreated')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_userAcc');
    }
};
