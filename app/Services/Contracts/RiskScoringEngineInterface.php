<?php

namespace App\Services\Contracts;

use App\Models\CountryRiskScore;

interface RiskScoringEngineInterface
{
    /**
     * Calculate and store the risk score for a single country.
     */
    public function calculateCountryScore(int $countryId, ?string $date = null): CountryRiskScore;

    /**
     * Recalculate and store risk scores for all countries in the system.
     */
    public function recalculateAllCountries(?string $date = null): void;
}
