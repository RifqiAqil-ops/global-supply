<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\Contracts\RiskScoringEngineInterface;
use App\Support\SyncTracker;
use Illuminate\Console\Command;
use Throwable;

class RecalculateRiskCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gscrip:recalculate-risk {--country= : Recalculate risk for a specific country code (ISO2/ISO3)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate composite risk scores for countries based on latest API feeds and weights';

    /**
     * Execute the console command.
     */
    public function handle(RiskScoringEngineInterface $engine)
    {
        $countryCode = $this->option('country');
        $startTime = microtime(true);
        SyncTracker::start('risk');

        if ($countryCode) {
            $code = strtoupper(trim($countryCode));
            $country = Country::where('iso2', $code)->orWhere('iso3', $code)->first();
            
            if (!$country) {
                SyncTracker::fail('risk', $startTime, "Country with code '{$code}' not found.");
                $this->error("Country with code '{$code}' not found.");
                return Command::FAILURE;
            }

            $this->info("Recalculating composite risk score for {$country->name}...");
            try {
                $score = $engine->calculateCountryScore($country->id);
                SyncTracker::success('risk', $startTime, 1);
                $this->info("Success! {$country->name} composite score: {$score->composite_score} ({$score->risk_level})");
            } catch (Throwable $e) {
                SyncTracker::fail('risk', $startTime, $e);
                $this->error("Error calculating score: " . $e->getMessage());
                return Command::FAILURE;
            }
        } else {
            $this->info("Recalculating composite risk scores for all countries...");

            try {
                $engine->recalculateAllCountries();
                $endTime = microtime(true);
                $duration = round($endTime - $startTime, 2);
                $totalCountries = Country::count();

                SyncTracker::success('risk', $startTime, $totalCountries);

                $this->info("Recalculation completed successfully in {$duration} seconds!");
            } catch (Throwable $e) {
                SyncTracker::fail('risk', $startTime, $e);
                $this->error("Failed to run composite risk recalculation: " . $e->getMessage());
                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
