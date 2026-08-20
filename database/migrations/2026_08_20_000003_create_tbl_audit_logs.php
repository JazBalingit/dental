<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_auditLogs', function (Blueprint $table) {
            $table->id('AuditLogID');
            $table->unsignedBigInteger('StaffID');
            $table->string('ActionType');
            $table->text('Description')->nullable();
            $table->boolean('IsArchived')->default(false);
            $table->timestamps();

            $table->foreign('StaffID')->references('UserID')->on('tbl_useraccount')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_auditLogs');
    }
};
