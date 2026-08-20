<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_activityLogs', function (Blueprint $table) {
            $table->id('ActivityLogsID');
            $table->unsignedBigInteger('UserID');
            $table->string('ActivityType')->default('Login');
            $table->timestamp('LoggedInTime')->nullable();
            $table->timestamp('LoggedOutTime')->nullable();
            $table->boolean('IsArchived')->default(false);

            $table->foreign('UserID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_activityLogs');
    }
};
