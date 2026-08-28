<?php
// Place in: database/migrations/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_services', function (Blueprint $table) {
            $table->unsignedBigInteger('CategoryID')->nullable()->after('ServiceName');
            // Deleting a category shouldn't delete its services — just
            // leave them uncategorized so nothing silently disappears.
            $table->foreign('CategoryID')->references('CategoryID')->on('tbl_service_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_services', function (Blueprint $table) {
            $table->dropForeign(['CategoryID']);
            $table->dropColumn('CategoryID');
        });
    }
};
