<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order matters: dependencies must be seeded before dependents.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            RiskCategorySeeder::class,
            RiskWeightSeeder::class,
            SystemConfigSeeder::class,
        ]);
    }
}
