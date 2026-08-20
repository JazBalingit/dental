<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_services', function (Blueprint $table) {
            $table->decimal('Price', 8, 2)->nullable()->after('Description');
            $table->boolean('IsArchived')->default(false)->after('Price');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_services', function (Blueprint $table) {
            $table->dropColumn(['Price', 'IsArchived', 'created_at', 'updated_at']);
        });
    }
};
