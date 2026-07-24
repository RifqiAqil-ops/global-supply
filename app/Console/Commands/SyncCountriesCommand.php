<?php

namespace App\Console\Commands;

use App\Services\External\RestCountriesService;
use App\Support\SyncTracker;
use Illuminate\Console\Command;
use Throwable;

class SyncCountriesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gscrip:sync-countries {--force : Force clear cache and refresh all data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize and cache country master data from the REST Countries API';

    /**
     * Execute the console command.
     */
    public function handle(RestCountriesService $service)
    {
        $this->info("Initializing REST Countries database synchronization...");
        
        if ($this->option('force')) {
            $this->warn("Force mode active. All country cache lists will be flushed.");
        }

        $this->info("Fetching data from REST Countries API (this may take a few seconds)...");
        
        $startTime = microtime(true);
        SyncTracker::start('countries');

        try {
            $summary = $service->syncAllCountries();
            
            // If any countries lack population data, auto-sync World Bank indicators
            $zeroPopCount = \App\Models\Country::where('population', '<=', 0)->count();
            if ($zeroPopCount > 0) {
                $this->info("Detected {$zeroPopCount} countries with unpopulated headcount. Syncing World Bank population indicator (SP.POP.TOTL)...");
                try {
                    app(\App\Services\External\WorldBankService::class)->syncEconomicIndicators();
                } catch (Throwable $e) {
                    $this->warn("World Bank population sync warning: " . $e->getMessage());
                }
            }

            // Warm up cache after synchronization
            $service->getAllCountries(true);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $totalCount = \App\Models\Country::count();

            SyncTracker::success('countries', $startTime, $totalCount);

            $this->line("");
            $this->info("Synchronization completed in {$duration} seconds!");
            
            $this->table(
                ['Metric', 'Count'],
                [
                    ['New Countries Added', $summary['new']],
                    ['Countries Updated', $summary['updated']],
                    ['Failed Sync Attempts', $summary['failed']],
                    ['Total Countries in Database', $totalCount]
                ]
            );

        } catch (Throwable $e) {
            SyncTracker::fail('countries', $startTime, $e);
            $this->error("Failed to run country synchronization: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
