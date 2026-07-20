<?php

namespace App\Console\Commands;

use App\Services\External\WorldBankService;
use App\Support\SyncTracker;
use Illuminate\Console\Command;
use Throwable;

class SyncWorldBankCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gscrip:sync-worldbank {--force : Clear cache before syncing} {--start-year=2019 : Start year for syncing data} {--end-year=2024 : End year for syncing data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize economic indicators from the World Bank API';

    /**
     * Execute the console command.
     */
    public function handle(WorldBankService $service)
    {
        $this->info("Initializing World Bank economic indicators synchronization...");

        $startYear = (int) $this->option('start-year');
        $endYear = (int) $this->option('end-year');

        if ($this->option('force')) {
            $this->warn("Force mode active. Cache will be flushed.");
            $service->flushCache();
        }

        $this->info("Syncing indicators for years {$startYear} to {$endYear}...");
        $startTime = microtime(true);
        SyncTracker::start('worldbank');

        try {
            $summary = $service->syncEconomicIndicators($startYear, $endYear);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $processed = $summary['countries_processed'] ?? 0;

            SyncTracker::success('worldbank', $startTime, $processed);

            $this->line("");
            $this->info("Synchronization completed in {$duration} seconds!");

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Countries Processed', $summary['countries_processed']],
                    ['New Records Created', $summary['new']],
                    ['Records Updated', $summary['updated']],
                    ['Failed Sync Attempts', $summary['failed']],
                    ['Total Indicators in Database', \App\Models\EconomicIndicator::count()]
                ]
            );

        } catch (Throwable $e) {
            SyncTracker::fail('worldbank', $startTime, $e);
            $this->error("Failed to run World Bank indicators synchronization: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
