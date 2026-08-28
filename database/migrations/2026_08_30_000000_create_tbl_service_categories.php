<?php
// Place in: database/migrations/
// Lets admins group services (e.g. "General Dentistry", "Orthodontic
// Treatment") so the landing page's "Our Services" section can be built
// from real data instead of hardcoded copy.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tbl_service_categories', function (Blueprint $table) {
            $table->id('CategoryID');
            $table->string('Name');
            $table->string('Icon')->nullable(); // Font Awesome class, e.g. "fa-solid fa-tooth"
            $table->unsignedInteger('DisplayOrder')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_service_categories');
    }
};
