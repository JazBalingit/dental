<?php

namespace Database\Seeders;

use App\Models\UserAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        UserAccount::updateOrCreate(
            ['Email' => 'admin@gmail.com'],
            [
                'Password' => Hash::make('admin123'),
                'AccountRole' => 'admin',
                'DateCreated' => now(),
                'EmailVerifiedAt' => now(),
            ]
        );
    }
}
