<?php
// Place in: database/migrations/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_services', function (Blueprint $table) {
            $table->id('ServiceID');
            $table->string('ServiceName');
            $table->text('Description')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_services');
    }
};