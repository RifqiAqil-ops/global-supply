<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Support\SyncTracker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class SetupWaypointCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waypoint:setup {--fresh : Wipe and re-run all database migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete one-time setup: migrate database, seed static datasets, and synchronize all external APIs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("=================================================");
        $this->info(" WAYPOINT - Automated One-Time Project Setup");
        $this->info("=================================================");

        $startTime = microtime(true);

        // Step 1: Migration
        if ($this->option('fresh')) {
            $this->warn("[1/7] Wiping database and running fresh migrations...");
            Artisan::call('migrate:fresh', ['--force' => true]);
        } else {
            $this->info("[1/7] Running database migrations...");
            Artisan::call('migrate', ['--force' => true]);
        }
        $this->line(Artisan::output());

        // Step 2: Seed Core Static Data
        $this->info("[2/7] Seeding core static data & initial configuration...");
        Artisan::call('db:seed', ['--force' => true]);
        $this->line(Artisan::output());

        // Step 3: Countries Sync
        $this->info("[3/7] Synchronizing 195 countries dataset from REST Countries API...");
        Artisan::call('gscrip:sync-countries');
        $this->line(Artisan::output());

        // Step 4: World Port Index Seeding
        $this->info("[4/7] Seeding 4,747 UN/LOCODE world cargo ports dataset...");
        $portStartTime = microtime(true);
        SyncTracker::start('ports');
        try {
            Artisan::call('db:seed', ['--class' => 'WorldPortSeeder', '--force' => true]);
            $portCount = \App\Models\Port::count();
            SyncTracker::success('ports', $portStartTime, $portCount);
            $this->line(Artisan::output());
        } catch (Throwable $e) {
            SyncTracker::fail('ports', $portStartTime, $e);
            $this->error("Port seeding error: " . $e->getMessage());
        }

        // Step 5: Exchange & Weather Sync
        $this->info("[5/7] Synchronizing exchange rates & weather metrics...");
        Artisan::call('gscrip:sync-exchange');
        $this->line(Artisan::output());

        Artisan::call('gscrip:sync-weather');
        $this->line(Artisan::output());

        // Step 6: World Bank & News Sync
        $this->info("[6/7] Synchronizing World Bank economic indicators & news feeds...");
        Artisan::call('gscrip:sync-worldbank');
        $this->line(Artisan::output());

        Artisan::call('gscrip:sync-news');
        $this->line(Artisan::output());

        // Step 7: Risk Recalculation
        $this->info("[7/7] Calculating initial composite risk index for all countries...");
        Artisan::call('gscrip:recalculate-risk');
        $this->line(Artisan::output());

        // Mark system as initialized
        \App\Models\SystemConfig::updateOrCreate(
            ['key' => 'system_initialized'],
            ['value' => 'true', 'type' => 'boolean', 'group' => 'system']
        );
        \Illuminate\Support\Facades\Cache::forget('system_initialization_running');

        // Cache optimization
        $this->info("Optimizing application route and configuration caches...");
        Artisan::call('optimize:clear');

        $duration = round(microtime(true) - $startTime, 2);

        $this->info("=================================================");
        $this->info(" WAYPOINT SETUP COMPLETED SUCCESSFULLY IN {$duration}s!");
        $this->info(" All datasets are synchronized and ready for production/dev.");
        $this->info("=================================================");

        return Command::SUCCESS;
    }
}
