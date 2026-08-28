<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_systemSettings', function (Blueprint $table) {
            $table->id('SystemSettingID');
            $table->string('SettingKey')->unique();
            $table->text('SettingValue')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_systemSettings');
    }
};
