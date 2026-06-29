<?php

namespace App\Console\Commands;

use App\Services\External\ExchangeRateService;
use Illuminate\Console\Command;
use Throwable;

class SyncExchangeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gscrip:sync-exchange {--force : Clear cache before syncing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize exchange rates for all countries from the ExchangeRate API';

    /**
     * Execute the console command.
     */
    public function handle(ExchangeRateService $service)
    {
        $this->info("Initializing ExchangeRate synchronization...");

        if ($this->option('force')) {
            $this->warn("Force mode active. Flushing exchange rate cache.");
            $service->flushCache();
        }

        $this->info("Fetching latest USD rates (single API request)...");
        $startTime = microtime(true);

        try {
            $summary = $service->syncAllRates();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->line("");
            $this->info("Exchange rate synchronization completed in {$duration} seconds!");

            $this->table(
                ['Metric', 'Count'],
                [
                    ['Unique Currencies Processed', $summary['currencies_processed']],
                    ['New Rate Records Created', $summary['new']],
                    ['Rate Records Updated', $summary['updated']],
                    ['Failed Sync Attempts', $summary['failed']],
                    ['Total Exchange Rate Records in DB', \App\Models\ExchangeRate::count()]
                ]
            );

        } catch (Throwable $e) {
            $this->error("Failed to run exchange rate synchronization: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
