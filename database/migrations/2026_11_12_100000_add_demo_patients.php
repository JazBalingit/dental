<?php

use Database\Seeders\DemoPatientsSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Adds the demo walk-in patients (6 inactive, 7 active — see
 * DemoPatientsSeeder) on the next deploy, since the hosted database is only
 * reachable through migrations. Never blocks a deploy: if something goes
 * wrong it is logged and the seeder can be run by hand later.
 *
 * Rolling back removes exactly those demo patients and their visits.
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            (new DemoPatientsSeeder)->run();
        } catch (\Throwable $e) {
            Log::warning('Demo patients were not added: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        (new DemoPatientsSeeder)->remove();
    }
};
