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
    }
}
