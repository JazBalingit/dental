<?php
// Place in: database/migrations/
// Categories could only ever be deleted (reassigning their services to
// "Uncategorized"). Switching to archive/unarchive — same pattern as
// Services and Appointment Process steps — needs a real flag instead.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_service_categories', function (Blueprint $table) {
            $table->boolean('IsArchived')->default(false)->after('DisplayOrder');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_service_categories', function (Blueprint $table) {
            $table->dropColumn('IsArchived');
        });
    }
};
