<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // No default accounts are seeded. The super admin is bootstrapped from
        // config/superadmin.php (.env) and then claimed into a real account;
        // every other admin is created in Staff Accounts.

        // Starter landing-page content (services, hero/about copy) so a
        // freshly provisioned database isn't blank. Both seeders only ever
        // create rows that don't already exist, so this is safe to re-run.
        $this->call([
            ServiceCategorySeeder::class,
            SystemSettingSeeder::class,
        ]);
    }
}
