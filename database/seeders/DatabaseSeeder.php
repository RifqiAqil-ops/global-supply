<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

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
            LexiconSeeder::class,
            ArticleSeeder::class,
            NewsArticleSeeder::class,
        ]);

        // Automatically trigger master dataset sync if database is unpopulated
        if (Country::count() === 0) {
            $this->command?->info("Database empty. Auto-initializing master datasets...");
            Artisan::call('gscrip:sync-countries');
            Artisan::call('gscrip:sync-worldbank');
            $this->call([WorldPortSeeder::class]);
            Artisan::call('gscrip:sync-exchange');
            Artisan::call('gscrip:sync-weather');
            Artisan::call('gscrip:recalculate-risk');
        } else {
            $this->call([WorldPortSeeder::class]);
        }
    }
}
