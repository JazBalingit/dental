<?php
// Place in: database/migrations/
// Every service now carries how long it takes. An appointment's total
// duration is the sum of its selected services' durations, rounded up to
// whole hourly slots — see app/Http/Controllers/Concerns/BuildsBookingCalendar.php.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tbl_services', function (Blueprint $table) {
            $table->unsignedInteger('DurationMinutes')->default(60)->after('Description');
        });
    }

    public function down(): void
    {
        Schema::table('tbl_services', function (Blueprint $table) {
            $table->dropColumn('DurationMinutes');
        });
    }
};
