<?php

use App\Models\Country;
use App\Models\EconomicIndicator;
use App\Services\External\WorldBankService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations to repair country population data in production database.
     */
    public function up(): void
    {
        try {
            $unpopulatedCount = Country::where('population', '<=', 0)->count();
            if ($unpopulatedCount > 0) {
                Log::info("Migration repairing population headcount for {$unpopulatedCount} countries...");

                // 1. First, check if EconomicIndicator table already has SP.POP.TOTL data
                $indicators = EconomicIndicator::where('indicator_code', 'SP.POP.TOTL')
                    ->where('value', '>', 0)
                    ->orderByDesc('year')
                    ->get()
                    ->unique('country_id');

                foreach ($indicators as $ind) {
                    Country::where('id', $ind->country_id)->update(['population' => (int) $ind->value]);
                }

                // 2. If countries still have zero population, run World Bank API sync
                $stillZero = Country::where('population', '<=', 0)->count();
                if ($stillZero > 0) {
                    try {
                        app(WorldBankService::class)->syncEconomicIndicators();
                    } catch (\Throwable $e) {
                        Log::warning("Migration World Bank sync warning: " . $e->getMessage());
                    }
                }

                // 3. Fallback for microstates (e.g. Vatican City)
                Country::where('iso2', 'VA')->where('population', '<=', 0)->update(['population' => 518]);
            }
        } catch (\Throwable $e) {
            Log::error("Population repair migration error: " . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reversal needed for data repair
    }
};
