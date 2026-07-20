<?php

namespace App\Jobs;

use App\Models\SystemConfig;
use App\Support\SyncTracker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class InitialSystemBootstrapJob implements ShouldQueue
{
    use Queueable;

    /**
     * Timeout for initial bootstrap execution (10 minutes).
     */
    public int $timeout = 600;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("InitialSystemBootstrapJob: Starting automated background setup sequence...");
        $startTime = microtime(true);

        try {
            // 1. Core Seeders
            Artisan::call('db:seed', ['--force' => true]);

            // 2. Sync Countries
            Artisan::call('gscrip:sync-countries');

            // 3. World Ports Seeding
            Artisan::call('db:seed', ['--class' => 'WorldPortSeeder', '--force' => true]);

            // 4. Exchange Rates Sync
            Artisan::call('gscrip:sync-exchange');

            // 5. Open-Meteo Weather Sync
            Artisan::call('gscrip:sync-weather');

            // 6. World Bank Indicators Sync
            Artisan::call('gscrip:sync-worldbank');

            // 7. GNews News Sync
            Artisan::call('gscrip:sync-news');

            // 8. Risk Scoring Recalculation
            Artisan::call('gscrip:recalculate-risk');

            // Mark system as initialized in database
            SystemConfig::updateOrCreate(
                ['key' => 'system_initialized'],
                [
                    'value' => 'true',
                    'type' => 'boolean',
                    'group' => 'system',
                    'label' => 'System Initialized Flag',
                    'description' => 'Indicates whether the application has completed initial master dataset synchronization.',
                    'is_editable' => false,
                ]
            );

            SystemConfig::updateOrCreate(
                ['key' => 'system_initialization_failed'],
                [
                    'value' => 'false',
                    'type' => 'boolean',
                    'group' => 'system',
                    'label' => 'Initialization Failed Flag',
                    'description' => 'Indicates whether automated initialization encountered errors.',
                    'is_editable' => false,
                ]
            );

            $duration = round(microtime(true) - $startTime, 2);
            Log::info("InitialSystemBootstrapJob: System auto-bootstrap completed successfully in {$duration}s!");

        } catch (\Throwable $e) {
            Log::error("InitialSystemBootstrapJob: Automatic setup failed: " . $e->getMessage());

            SystemConfig::updateOrCreate(
                ['key' => 'system_initialization_failed'],
                [
                    'value' => 'true',
                    'type' => 'boolean',
                    'group' => 'system',
                    'label' => 'Initialization Failed Flag',
                    'description' => 'Indicates whether automated initialization encountered errors.',
                    'is_editable' => false,
                ]
            );
        } finally {
            Cache::forget('system_initialization_running');
        }
    }
}
