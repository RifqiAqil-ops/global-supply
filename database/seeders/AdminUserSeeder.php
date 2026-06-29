<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default admin user.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gscrip.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create a demo regular user
        User::updateOrCreate(
            ['email' => 'user@gscrip.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
