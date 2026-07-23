<?php

namespace App\Services;

use App\Models\Port;
use App\Models\Country;
use App\Models\CountryRiskScore;
use App\Models\WeatherData;
use App\Models\ExchangeRate;
use App\Models\NewsArticle;
use App\Services\MaritimeRoutingService;

class RouteAnalysisService
{
    protected MaritimeRoutingService $maritimeRoutingService;

    public function __construct(MaritimeRoutingService $maritimeRoutingService)
    {
        $this->maritimeRoutingService = $maritimeRoutingService;
    }

    /**
     * Analyze route parameters using true maritime sea lanes.
     */
    public function analyze(int $originPortId, int $destinationPortId, string $priority = 'safest', string $containerType = 'container'): array
    {
        $origin = Port::with(['country.latestRiskScore', 'country.latestWeather', 'country.exchangeRates'])->findOrFail($originPortId);
        $destination = Port::with(['country.latestRiskScore', 'country.latestWeather', 'country.exchangeRates'])->findOrFail($destinationPortId);

        // 1. Compute Primary Sea Route Polyline & Waypoints via Maritime Routing Service
        $seaRoute = $this->maritimeRoutingService->calculateSeaRoute(
            (float)$origin->latitude, (float)$origin->longitude,
            (float)$destination->latitude, (float)$destination->longitude
        );

        // 2. Build Transit Timeline Steps
        $timeline = [];
        $waypointRiskScores = [];
        $weatherAlerts = [];

        // Origin Node (Step 1)
        $originRisk = $origin->country && $origin->country->latestRiskScore ? (float)$origin->country->latestRiskScore->composite_score : 35.0;
        $originRiskLevel = $origin->country && $origin->country->latestRiskScore ? $origin->country->latestRiskScore->risk_level : 'Low';
        $originWeather = $origin->country && $origin->country->latestWeather ? $origin->country->latestWeather->temperature . '°C, ' . $origin->country->latestWeather->weather_description : 'Normal Sea Conditions';

        $waypointRiskScores[] = $originRisk;
        $timeline[] = [
            'step' => 1,
            'type' => 'Origin',
            'port_id' => $origin->id,
            'port_name' => $origin->name,
            'port_code' => $origin->port_code ?? $origin->un_locode,
            'country_name' => $origin->country ? $origin->country->name : 'Unknown',
            'latitude' => (float)$origin->latitude,
            'longitude' => (float)$origin->longitude,
            'status' => $origin->is_active ? 'Operational' : 'Inactive',
            'risk_score' => $originRisk,
            'risk_level' => $originRiskLevel,
            'weather' => $originWeather,
            'congestion' => $this->calculateCongestionIndex($origin) . '%',
        ];

        // Sea Passage / Chokepoint Nodes
        $stepCounter = 2;
        foreach ($seaRoute['waypoints'] as $wp) {
            $wpRisk = $wp['type'] === 'Chokepoint' || $wp['type'] === 'Canal' ? 55.0 : 30.0;
            if (!empty($wp['warning'])) {
                $wpRisk += 15.0;
            }
            $waypointRiskScores[] = $wpRisk;

            $timeline[] = [
                'step' => $stepCounter++,
                'type' => $wp['type'] === 'Chokepoint' || $wp['type'] === 'Canal' ? 'Maritime Chokepoint' : 'Sea Passage',
                'port_id' => null,
                'port_name' => $wp['name'],
                'port_code' => $wp['id'],
                'country_name' => $wp['country'] ?? 'International Waters',
                'latitude' => (float)$wp['lat'],
                'longitude' => (float)$wp['lng'],
                'status' => 'Navigable Lane',
                'risk_score' => $wpRisk,
                'risk_level' => $wpRisk >= 65 ? 'High' : ($wpRisk >= 40 ? 'Medium' : 'Low'),
                'weather' => 'Marine Current Monitoring',
                'congestion' => ($wp['type'] === 'Canal' ? '78%' : '45%'),
                'warning' => $wp['warning'] ?? null,
            ];
        }

        // Destination Node (Final Step)
        $destRisk = $destination->country && $destination->country->latestRiskScore ? (float)$destination->country->latestRiskScore->composite_score : 30.0;
        $destRiskLevel = $destination->country && $destination->country->latestRiskScore ? $destination->country->latestRiskScore->risk_level : 'Low';
        $destWeather = $destination->country && $destination->country->latestWeather ? $destination->country->latestWeather->temperature . '°C, ' . $destination->country->latestWeather->weather_description : 'Normal Sea Conditions';

        $waypointRiskScores[] = $destRisk;
        $timeline[] = [
            'step' => $stepCounter,
            'type' => 'Destination',
            'port_id' => $destination->id,
            'port_name' => $destination->name,
            'port_code' => $destination->port_code ?? $destination->un_locode,
            'country_name' => $destination->country ? $destination->country->name : 'Unknown',
            'latitude' => (float)$destination->latitude,
            'longitude' => (float)$destination->longitude,
            'status' => $destination->is_active ? 'Operational' : 'Inactive',
            'risk_score' => $destRisk,
            'risk_level' => $destRiskLevel,
            'weather' => $destWeather,
            'congestion' => $this->calculateCongestionIndex($destination) . '%',
        ];

        // 3. Distance & ETA Calculations
        $totalDistanceNM = $seaRoute['total_distance_nm'];
        $vesselSpeedKnots = 18.0; // Standard commercial vessel cruising speed in knots

        $sailingHours = $totalDistanceNM / $vesselSpeedKnots;
        $chokepointDelayHours = count($seaRoute['chokepoints']) * 8;
        $totalHours = $sailingHours + $chokepointDelayHours;
        $etaDays = round($totalHours / 24, 1);

        // 4. Composite Risk Calculations
        $avgRiskScore = count($waypointRiskScores) > 0 ? array_sum($waypointRiskScores) / count($waypointRiskScores) : 35;
        $chokepointPenalty = count($seaRoute['warnings']) * 5;
        $overallRiskScore = round(min(100, max(1, $avgRiskScore + $chokepointPenalty)), 1);
        $overallRiskLevel = $overallRiskScore >= 65 ? 'High' : ($overallRiskScore >= 40 ? 'Medium' : 'Low');

        // 5. Macro Data (Exchange Rates, News, Congestion)
        $newsArticles = NewsArticle::orderBy('published_at', 'desc')->take(3)->get();
        $geopoliticalStatus = $newsArticles->count() > 0 
            ? 'Monitored: ' . $newsArticles->first()->title
            : 'Data belum tersedia.';

        $destCurrency = $destination->country ? $destination->country->currency_code : 'USD';
        $exchangeRate = ExchangeRate::where('currency_code', $destCurrency)->first();
        $currencyImpact = $exchangeRate 
            ? "1 USD = {$exchangeRate->rate_to_usd} {$destCurrency} (" . ($exchangeRate->change_percent >= 0 ? '+' : '') . "{$exchangeRate->change_percent}%)"
            : 'Stable / Standard USD Settlement';

        $avgCongestion = count($timeline) > 0 
            ? round(array_sum(array_map(fn($t) => (float)str_replace('%', '', $t['congestion']), $timeline)) / count($timeline))
            : 30;

        $recommendation = match(true) {
            $overallRiskScore < 40 => 'Optimal & Safe Maritime Lane - Standard Cruising Schedule Recommended',
            $overallRiskScore < 65 => 'Moderate Caution - Escort Patrols & Chokepoint Traffic Monitoring Advised',
            default => 'High Risk Alert - Consider Alternative Cape Bypass or Rerouting Protocols',
        };

        // 6. Advisory Commentary Generation
        $advisoryText = "Rute pelayaran laut dari {$origin->name} menuju {$destination->name} melintasi jalur maritim sepanjang " . number_format($totalDistanceNM, 1) . " NM dengan estimasi transit {$etaDays} Hari. " .
            (count($seaRoute['warnings']) > 0 
                ? "Ditemukan " . count($seaRoute['warnings']) . " area chokepoint berisiko: " . implode(', ', array_map(fn($w) => $w['name'], $seaRoute['warnings'])) . "."
                : "Koridor maritim kondusif tanpa kendala navigasi utama.");

        // 7. Alternative Sea Route Calculation
        $alternativeRoute = null;
        if (count($seaRoute['warnings']) > 0 || $overallRiskScore >= 45) {
            $avoidWps = array_map(fn($w) => $w['waypoint_id'], $seaRoute['warnings']);
            $avoidWps[] = 'SUEZ_CANAL';
            $avoidWps[] = 'BAB_EL_MANDEB';
            $avoidWps[] = 'RED_SEA_MID';

            $altSeaRoute = $this->maritimeRoutingService->calculateSeaRoute(
                (float)$origin->latitude, (float)$origin->longitude,
                (float)$destination->latitude, (float)$destination->longitude,
                $avoidWps
            );

            $altDistanceNM = $altSeaRoute['total_distance_nm'];
            $altEtaDays = round(($altDistanceNM / 18.0) / 24, 1);
            $altRiskScore = round(max(15, $overallRiskScore - 22.5), 1);
            $savingsPercent = round((($overallRiskScore - $altRiskScore) / $overallRiskScore) * 100, 1);

            $alternativeRoute = [
                'original' => [
                    'route_summary' => "{$origin->name} → {$destination->name} (Direct Lane)",
                    'risk_score' => $overallRiskScore,
                    'eta_days' => $etaDays,
                ],
                'alternative' => [
                    'route_summary' => "{$origin->name} → Cape Bypass → {$destination->name}",
                    'risk_score' => $altRiskScore,
                    'eta_days' => $altEtaDays,
                    'savings_risk_percent' => $savingsPercent,
                    'recommendation_text' => "Rute laut alternatif via Cape Bypass menurunkan tingkat risiko sebesar {$savingsPercent}% dengan penyesuaian waktu pelayaran maritim.",
                    'coordinates' => $altSeaRoute['coordinates'],
                ]
            ];
        }

        return [
            'origin' => [
                'id' => $origin->id,
                'name' => $origin->name,
                'country' => $origin->country ? $origin->country->name : '',
                'latitude' => (float)$origin->latitude,
                'longitude' => (float)$origin->longitude,
            ],
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->name,
                'country' => $destination->country ? $destination->country->name : '',
                'latitude' => (float)$destination->latitude,
                'longitude' => (float)$destination->longitude,
            ],
            'summary' => [
                'distance_nm' => $totalDistanceNM,
                'distance_km' => round($totalDistanceNM * 1.852, 1),
                'eta_days' => $etaDays,
                'risk_score' => $overallRiskScore,
                'risk_level' => $overallRiskLevel,
                'weather_summary' => 'Normal Maritime Conditions',
                'currency_impact' => $currencyImpact,
                'port_congestion' => $avgCongestion . '%',
                'geopolitical_status' => $geopoliticalStatus,
                'recommendation' => $recommendation,
            ],
            'sea_polyline' => $seaRoute['coordinates'],
            'chokepoints' => $seaRoute['chokepoints'],
            'warnings' => $seaRoute['warnings'],
            'ai_insight' => $advisoryText,
            'timeline' => $timeline,
            'alternative_route' => $alternativeRoute,
        ];
    }

    /**
     * Calculate port congestion index.
     */
    protected function calculateCongestionIndex(Port $port): int
    {
        $base = match($port->harbor_size) {
            'Very Large' => 30,
            'Large' => 25,
            'Medium' => 45,
            default => 60,
        };
        return min(95, max(10, $base + ($port->id % 25)));
    }
}
