<?php

namespace App\Services\Internal;

use App\Models\Country;
use App\Models\CountryRiskScore;
use App\Models\RiskCategory;
use App\Models\RiskWeight;
use App\Models\RiskScoreDetail;
use App\Models\ExchangeRate;
use App\Services\Contracts\RiskScoringEngineInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RiskScoringEngine implements RiskScoringEngineInterface
{
    /**
     * Calculate and store the risk score for a single country.
     */
    public function calculateCountryScore(int $countryId, ?string $date = null): CountryRiskScore
    {
        $scoreDate = $date ?? Carbon::now()->toDateString();

        // Retrieve country with eager loaded details to prevent N+1
        $country = Country::with(['latestWeather', 'ports', 'economicIndicators', 'newsArticles'])->find($countryId);
        if (!$country) {
            throw new \InvalidArgumentException("Country ID {$countryId} not found.");
        }

        // 1. Economic Risk calculation
        $gdpInd = $country->economicIndicators->where('indicator_code', 'NY.GDP.MKTP.CD')->sortByDesc('year')->first();
        $capitaInd = $country->economicIndicators->where('indicator_code', 'NY.GDP.PCAP.CD')->sortByDesc('year')->first();
        $inflationInd = $country->economicIndicators->where('indicator_code', 'FP.CPI.TOTL.ZG')->sortByDesc('year')->first();

        $gdp = $gdpInd ? (float)$gdpInd->value : null;
        $capita = $capitaInd ? (float)$capitaInd->value : null;
        $inflation = $inflationInd ? (float)$inflationInd->value : null;

        // GDP Score (higher is lower risk, 10B to 1T USD scale)
        if ($gdp === null) {
            $gdpScore = 50.0;
        } else {
            $gdpScore = 100.0 - ((min(max($gdp, 1e10), 1e12) - 1e10) / (1e12 - 1e10) * 100.0);
        }

        // GDP Per Capita Score (higher is lower risk, 1k to 60k USD scale)
        if ($capita === null) {
            $capitaScore = 50.0;
        } else {
            $capitaScore = 100.0 - ((min(max($capita, 1000.0), 60000.0) - 1000.0) / (60000.0 - 1000.0) * 100.0);
        }

        // Inflation Score (boundary: <=2.0% is 0, >=20.0% is 100)
        if ($inflation === null) {
            $inflationScore = 40.0;
        } else {
            if ($inflation < 0) {
                $inflationScore = min(50.0, abs($inflation) * 5.0); // Deflation penalty
            } else if ($inflation <= 2.0) {
                $inflationScore = 0.0;
            } else {
                $inflationScore = (min(max($inflation, 2.0), 20.0) - 2.0) / 18.0 * 100.0;
            }
        }
        $economicScore = ($gdpScore + $capitaScore + $inflationScore) / 3.0;

        // 2. Weather Risk calculation
        $weather = $country->latestWeather;
        if (!$weather) {
            $weatherScore = 40.0;
            $weatherDataLog = ['status' => 'No weather data available'];
        } else {
            $temp = (float)$weather->temperature;
            $wind = (float)$weather->wind_speed;
            $humidity = (float)$weather->humidity;
            $precip = (float)$weather->precipitation;
            $isExtreme = (bool)$weather->is_extreme;

            $tempRisk = (min(max(abs($temp - 20.0), 0.0), 18.0) / 18.0) * 100.0;
            $windRisk = ((min(max($wind, 15.0), 60.0) - 15.0) / 45.0) * 100.0;
            $humidityRisk = (min(max(abs($humidity - 55.0), 0.0), 35.0) / 35.0) * 100.0;
            $precipRisk = (min(max($precip, 0.0), 20.0) / 20.0) * 100.0;

            $baseWeather = ($tempRisk + $windRisk + $humidityRisk + $precipRisk) / 4.0;
            $weatherScore = $isExtreme ? max($baseWeather, 85.0) : $baseWeather;

            $weatherDataLog = [
                'temperature' => $temp,
                'wind_speed' => $wind,
                'humidity' => $humidity,
                'precipitation' => $precip,
                'is_extreme' => $isExtreme
            ];
        }

        // 3. Currency Risk calculation
        $exchangeRate = null;
        if ($country->currency_code) {
            $exchangeRate = ExchangeRate::where('currency_code', $country->currency_code)
                ->where('rate_date', '<=', $scoreDate)
                ->latest('rate_date')
                ->first();
        }

        if (!$exchangeRate) {
            $currencyScore = 30.0;
            $currencyDataLog = ['status' => 'No currency rates available'];
        } else {
            $change = (float)$exchangeRate->change_percent;
            // Bound change_percent between 0.1% and 2.0% absolute volatility
            $currencyScore = max(15.0, (min(max(abs($change), 0.1), 2.0) - 0.1) / 1.9 * 100.0);
            $currencyDataLog = [
                'currency_code' => $exchangeRate->currency_code,
                'change_percent' => $change,
                'rate_to_usd' => (float)$exchangeRate->rate_to_usd
            ];
        }

        // 4. Geopolitical Risk calculation (Sentiment analysis)
        $news = $country->newsArticles;
        $totalNews = $news->count();
        if ($totalNews === 0) {
            $geopoliticalScore = 35.0;
            $newsDataLog = ['status' => 'No articles available'];
        } else {
            $negative = $news->where('sentiment', 'negative')->count();
            $positive = $news->where('sentiment', 'positive')->count();
            $negRatio = $negative / $totalNews;
            $volumePenalty = min(20.0, $negative * 4.0);
            $posMitigation = ($positive / $totalNews) * 15.0;
            $geopoliticalScore = min(100.0, max(10.0, ($negRatio * 80.0) + $volumePenalty - $posMitigation));
            $newsDataLog = [
                'total_news' => $totalNews,
                'negative_news' => $negative,
                'positive_news' => $positive,
                'neg_ratio' => $negRatio
            ];
        }

        // 5. Logistics Risk calculation (Ports infrastructure)
        $ports = $country->ports;
        $portsCount = $ports->count();
        if ($portsCount === 0) {
            $logisticsScore = 75.0; // Higher risk for landlocked countries
            $portsDataLog = ['ports_count' => 0];
        } else {
            $inactiveCount = $ports->where('is_active', false)->count();
            $maxDepth = $ports->max('max_depth');
            $hasLargePort = $ports->where('port_size', 'large')->isNotEmpty();

            if ($portsCount === 1) {
                $baseRisk = 50.0;
            } else if ($portsCount === 2) {
                $baseRisk = 35.0;
            } else {
                $baseRisk = 20.0;
            }

            $depth = $maxDepth !== null ? (float)$maxDepth : 15.0;
            $depthRisk = $depth >= 15.0 ? 0.0 : ($depth >= 12.0 ? 10.0 : 25.0);
            $inactiveRisk = ($inactiveCount / $portsCount) * 50.0;
            $largePortBonus = $hasLargePort ? 10.0 : 0.0;

            $logisticsScore = min(100.0, max(10.0, $baseRisk + $depthRisk + $inactiveRisk - $largePortBonus));
            $portsDataLog = [
                'ports_count' => $portsCount,
                'inactive_ports' => $inactiveCount,
                'max_depth' => $maxDepth,
                'has_large_port' => $hasLargePort
            ];
        }

        // Retrieve weights from DB dynamically
        $weights = RiskWeight::all()->keyBy('riskCategory.slug');

        $economicWeight = isset($weights['economic-risk']) ? (float)$weights['economic-risk']->weight : 0.20;
        $weatherWeight = isset($weights['weather-risk']) ? (float)$weights['weather-risk']->weight : 0.20;
        $currencyWeight = isset($weights['currency-stability-risk']) ? (float)$weights['currency-stability-risk']->weight : 0.20;
        $geopoliticalWeight = isset($weights['geopolitical-risk']) ? (float)$weights['geopolitical-risk']->weight : 0.20;
        $logisticsWeight = isset($weights['logistics-risk']) ? (float)$weights['logistics-risk']->weight : 0.20;

        // Ensure weights sum to 1.0 (re-normalize if needed)
        $weightSum = $economicWeight + $weatherWeight + $currencyWeight + $geopoliticalWeight + $logisticsWeight;
        if ($weightSum > 0 && abs($weightSum - 1.0) > 0.001) {
            $economicWeight /= $weightSum;
            $weatherWeight /= $weightSum;
            $currencyWeight /= $weightSum;
            $geopoliticalWeight /= $weightSum;
            $logisticsWeight /= $weightSum;
        }

        $weightedEconomic = $economicScore * $economicWeight;
        $weightedWeather = $weatherScore * $weatherWeight;
        $weightedCurrency = $currencyScore * $currencyWeight;
        $weightedGeopolitical = $geopoliticalScore * $geopoliticalWeight;
        $weightedLogistics = $logisticsScore * $logisticsWeight;

        $compositeScore = $weightedEconomic + $weightedWeather + $weightedCurrency + $weightedGeopolitical + $weightedLogistics;
        $compositeScore = min(100.0, max(0.0, $compositeScore));

        // Classify risk level
        if ($compositeScore <= 30.0) {
            $riskLevel = 'low';
        } else if ($compositeScore <= 60.0) {
            $riskLevel = 'medium';
        } else {
            $riskLevel = 'high';
        }

        // Find previous score to compute change
        $previous = CountryRiskScore::where('country_id', $countryId)
            ->where('score_date', '<', $scoreDate)
            ->orderBy('score_date', 'desc')
            ->first();

        $previousScore = $previous ? (float)$previous->composite_score : null;
        $scoreChange = $previousScore !== null ? ($compositeScore - $previousScore) : 0.0;

        // Record or update score entry
        return DB::transaction(function () use (
            $countryId, $scoreDate, $compositeScore, $riskLevel, $previousScore, $scoreChange,
            $economicScore, $weightedEconomic, $gdpScore, $capitaScore, $inflationScore,
            $weatherScore, $weightedWeather, $weatherDataLog,
            $currencyScore, $weightedCurrency, $currencyDataLog,
            $geopoliticalScore, $weightedGeopolitical, $newsDataLog,
            $logisticsScore, $weightedLogistics, $portsDataLog
        ) {
            $riskScoreModel = CountryRiskScore::updateOrCreate(
                [
                    'country_id' => $countryId,
                    'score_date' => $scoreDate
                ],
                [
                    'composite_score' => $compositeScore,
                    'risk_level' => $riskLevel,
                    'previous_score' => $previousScore,
                    'score_change' => $scoreChange,
                    'data_completeness' => 100.0,
                    'calculated_at' => Carbon::now()
                ]
            );

            $categories = RiskCategory::all()->keyBy('slug');

            // 1. Economic Risk Details
            if (isset($categories['economic-risk'])) {
                RiskScoreDetail::updateOrCreate(
                    [
                        'country_risk_score_id' => $riskScoreModel->id,
                        'risk_category_id' => $categories['economic-risk']->id
                    ],
                    [
                        'category_score' => $economicScore,
                        'weighted_score' => $weightedEconomic,
                        'scoring_data' => [
                            'gdp_score' => $gdpScore,
                            'capita_score' => $capitaScore,
                            'inflation_score' => $inflationScore
                        ],
                        'notes' => 'Calculated from latest World Bank economic indicators'
                    ]
                );
            }

            // 2. Weather Risk Details
            if (isset($categories['weather-risk'])) {
                RiskScoreDetail::updateOrCreate(
                    [
                        'country_risk_score_id' => $riskScoreModel->id,
                        'risk_category_id' => $categories['weather-risk']->id
                    ],
                    [
                        'category_score' => $weatherScore,
                        'weighted_score' => $weightedWeather,
                        'scoring_data' => $weatherDataLog,
                        'notes' => 'Calculated from Open-Meteo local weather readings'
                    ]
                );
            }

            // 3. Currency Stability Risk Details
            if (isset($categories['currency-stability-risk'])) {
                RiskScoreDetail::updateOrCreate(
                    [
                        'country_risk_score_id' => $riskScoreModel->id,
                        'risk_category_id' => $categories['currency-stability-risk']->id
                    ],
                    [
                        'category_score' => $currencyScore,
                        'weighted_score' => $weightedCurrency,
                        'scoring_data' => $currencyDataLog,
                        'notes' => 'Calculated from latest currency exchange volatility'
                    ]
                );
            }

            // 4. Geopolitical Risk Details
            if (isset($categories['geopolitical-risk'])) {
                RiskScoreDetail::updateOrCreate(
                    [
                        'country_risk_score_id' => $riskScoreModel->id,
                        'risk_category_id' => $categories['geopolitical-risk']->id
                    ],
                    [
                        'category_score' => $geopoliticalScore,
                        'weighted_score' => $weightedGeopolitical,
                        'scoring_data' => $newsDataLog,
                        'notes' => 'Calculated from GNews sentiment heuristics analysis'
                    ]
                );
            }

            // 5. Logistics Risk Details
            if (isset($categories['logistics-risk'])) {
                RiskScoreDetail::updateOrCreate(
                    [
                        'country_risk_score_id' => $riskScoreModel->id,
                        'risk_category_id' => $categories['logistics-risk']->id
                    ],
                    [
                        'category_score' => $logisticsScore,
                        'weighted_score' => $weightedLogistics,
                        'scoring_data' => $portsDataLog,
                        'notes' => 'Calculated from logistics ports infrastructure layout'
                    ]
                );
            }

            return $riskScoreModel;
        });

        // Trigger Alert Engine
        $thresholds = [30, 60, 80];
        foreach ($thresholds as $T) {
            if ($previousScore !== null && $previousScore < $T && $compositeScore >= $T) {
                \App\Models\ActivityLog::create([
                    'action' => 'risk_alert',
                    'model_type' => Country::class,
                    'model_id' => $countryId,
                    'description' => "Risk alert: {$country->name} risk score rose from " . number_format($previousScore, 2) . " to " . number_format($compositeScore, 2) . " (crossed threshold {$T})",
                    'old_values' => ['composite_score' => $previousScore],
                    'new_values' => ['composite_score' => $compositeScore],
                    'ip_address' => request() ? request()->ip() : '127.0.0.1',
                    'user_agent' => request() ? request()->userAgent() : 'System'
                ]);
            }
        }

        return $riskScoreModel;
    }

    /**
     * Recalculate and store risk scores for all countries in the system.
     */
    public function recalculateAllCountries(?string $date = null): void
    {
        $countries = Country::all();
        foreach ($countries as $country) {
            try {
                $this->calculateCountryScore($country->id, $date);
            } catch (\Exception $e) {
                // Log and continue to avoid blocking other countries
                \Illuminate\Support\Facades\Log::error("Failed to calculate risk score for {$country->name}: " . $e->getMessage());
            }
        }
    }
}
