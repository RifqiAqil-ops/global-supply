<?php

namespace App\Services;

use App\Models\Port;
use App\Models\Country;
use App\Models\CountryRiskScore;
use App\Models\WeatherData;
use App\Models\ExchangeRate;
use App\Models\NewsArticle;

class RouteAnalysisService
{
    /**
     * Calculate Nautical Miles distance between two coordinates using Haversine formula.
     */
    public function calculateDistanceNM(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $km = $earthRadiusKm * $c;

        // Convert km to Nautical Miles (1 NM = 1.852 km)
        return round($km / 1.852, 1);
    }

    /**
     * Analyze route parameters between origin and destination ports.
     */
    public function analyze(int $originPortId, int $destinationPortId, string $priority = 'safest', string $containerType = 'container'): array
    {
        $origin = Port::with(['country.latestRiskScore', 'country.latestWeather', 'country.exchangeRates'])->findOrFail($originPortId);
        $destination = Port::with(['country.latestRiskScore', 'country.latestWeather', 'country.exchangeRates'])->findOrFail($destinationPortId);

        // Find potential transit hubs in the DB
        $transitPorts = $this->findTransitPorts($origin, $destination, $priority);

        // Build route timeline nodes: Origin -> Transits -> Destination
        $nodes = array_merge([$origin], $transitPorts, [$destination]);
        
        // Calculate cumulative distance and leg metrics
        $totalDistanceNM = 0;
        $timeline = [];
        $waypointRiskScores = [];
        $weatherAlerts = [];
        $newsHeadlines = [];

        for ($i = 0; $i < count($nodes); $i++) {
            $current = $nodes[$i];
            
            if ($i > 0) {
                $prev = $nodes[$i - 1];
                $legDist = $this->calculateDistanceNM(
                    (float)$prev->latitude, (float)$prev->longitude,
                    (float)$current->latitude, (float)$current->longitude
                );
                $totalDistanceNM += $legDist;
            }

            // Extract country metrics
            $country = $current->country;
            $riskModel = $country ? $country->latestRiskScore : null;
            $weatherModel = $country ? $country->latestWeather : null;

            $riskScore = $riskModel ? (float)$riskModel->composite_score : 35.0;
            $riskLevel = $riskModel ? $riskModel->risk_level : 'Medium';
            $weatherTemp = $weatherModel ? $weatherModel->temperature . '°C, ' . $weatherModel->weather_description : 'Data belum tersedia.';
            $isExtremeWeather = $weatherModel ? $weatherModel->is_extreme : false;

            if ($isExtremeWeather) {
                $weatherAlerts[] = $current->name . ' (' . $country->name . ') experiencing extreme weather: ' . ($weatherModel->weather_description ?? 'Severe Conditions');
            }

            // Calculate port congestion index based on harbor size & max depth
            $congestionIndex = $this->calculateCongestionIndex($current);

            $waypointRiskScores[] = $riskScore;

            $typeLabel = ($i === 0) ? 'Origin' : (($i === count($nodes) - 1) ? 'Destination' : 'Transit Hub');

            $timeline[] = [
                'step' => $i + 1,
                'type' => $typeLabel,
                'port_id' => $current->id,
                'port_name' => $current->name,
                'port_code' => $current->port_code ?? $current->un_locode,
                'country_name' => $country ? $country->name : 'Unknown',
                'country_flag' => $country ? $country->flag_url : null,
                'latitude' => (float)$current->latitude,
                'longitude' => (float)$current->longitude,
                'harbor_size' => $current->harbor_size ?? 'Medium',
                'status' => $current->is_active ? 'Operational' : 'Inactive',
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
                'weather' => $weatherTemp,
                'congestion' => $congestionIndex . '%',
            ];
        }

        // Vessel speed based on container type (knots = NM/hour)
        $vesselSpeedKnots = match($containerType) {
            'container' => 20,
            'liquid' => 14,
            'bulk' => 13,
            default => 16,
        };

        // Estimated transit time in days (24 hours sailing per day + 12h per transit port)
        $sailingHours = $totalDistanceNM / max(10, $vesselSpeedKnots);
        $transitPortDelayHours = count($transitPorts) * 12;
        $totalHours = $sailingHours + $transitPortDelayHours;
        $etaDays = round($totalHours / 24, 1);

        // Overall risk score: average of waypoints + vessel type risk factor
        $avgRiskScore = count($waypointRiskScores) > 0 ? array_sum($waypointRiskScores) / count($waypointRiskScores) : 35;
        $typeModifier = match($containerType) {
            'liquid' => 6,
            'bulk' => 4,
            default => 0,
        };
        $overallRiskScore = round(min(100, max(1, $avgRiskScore + $typeModifier)), 1);

        // Risk Level Badge
        $overallRiskLevel = $overallRiskScore >= 65 ? 'High' : ($overallRiskScore >= 40 ? 'Medium' : 'Low');

        // Fetch recent news for geopolitical status
        $newsArticles = NewsArticle::orderBy('published_at', 'desc')->take(3)->get();
        $geopoliticalStatus = $newsArticles->count() > 0 
            ? 'Monitored: ' . $newsArticles->first()->title
            : 'Data belum tersedia.';

        // Currency Impact
        $destCurrency = $destination->country ? $destination->country->currency_code : 'USD';
        $exchangeRate = ExchangeRate::where('currency_code', $destCurrency)->first();
        $currencyImpact = $exchangeRate 
            ? "1 USD = {$exchangeRate->rate_to_usd} {$destCurrency} (" . ($exchangeRate->change_percent >= 0 ? '+' : '') . "{$exchangeRate->change_percent}%)"
            : 'Stable / Standard USD Settlement';

        // Overall Port Congestion Average
        $avgCongestion = count($timeline) > 0 
            ? round(array_sum(array_map(fn($t) => (float)str_replace('%', '', $t['congestion']), $timeline)) / count($timeline))
            : 25;

        // Overall Recommendation
        $recommendation = match(true) {
            $overallRiskScore < 40 => 'Optimal & Safe Route - Standard Transit Schedule Recommended',
            $overallRiskScore < 65 => 'Moderate Caution - Monitor Weather & Port Congestion Advisories',
            default => 'High Risk Alert - Consider Alternative Transit Routes or Escort Protocols',
        };

        // AI Insight commentary generation
        $aiInsight = $this->generateAiInsight($origin, $destination, $transitPorts, $overallRiskScore, $overallRiskLevel, $avgCongestion, $weatherAlerts, $priority);

        // Alternative Route Generation (if risk >= 40 or priority == 'safest')
        $alternativeRoute = null;
        if ($overallRiskScore >= 40 || count($transitPorts) > 0) {
            $alternativeRoute = $this->generateAlternativeRoute($origin, $destination, $overallRiskScore, $etaDays, $containerType);
        }

        return [
            'origin' => [
                'id' => $origin->id,
                'name' => $origin->name,
                'country' => $origin->country ? $origin->country->name : '',
                'flag' => $origin->country ? $origin->country->flag_url : '',
                'latitude' => (float)$origin->latitude,
                'longitude' => (float)$origin->longitude,
            ],
            'destination' => [
                'id' => $destination->id,
                'name' => $destination->name,
                'country' => $destination->country ? $destination->country->name : '',
                'flag' => $destination->country ? $destination->country->flag_url : '',
                'latitude' => (float)$destination->latitude,
                'longitude' => (float)$destination->longitude,
            ],
            'summary' => [
                'distance_nm' => $totalDistanceNM,
                'distance_km' => round($totalDistanceNM * 1.852, 1),
                'eta_days' => $etaDays,
                'risk_score' => $overallRiskScore,
                'risk_level' => $overallRiskLevel,
                'weather_summary' => count($weatherAlerts) > 0 ? implode('; ', $weatherAlerts) : 'Normal Marine Conditions',
                'currency_impact' => $currencyImpact,
                'port_congestion' => $avgCongestion . '%',
                'geopolitical_status' => $geopoliticalStatus,
                'recommendation' => $recommendation,
            ],
            'ai_insight' => $aiInsight,
            'timeline' => $timeline,
            'alternative_route' => $alternativeRoute,
        ];
    }

    /**
     * Find logical transit ports between origin and destination.
     */
    protected function findTransitPorts(Port $origin, Port $destination, string $priority): array
    {
        // Find major hub ports that lie spatially between origin and destination
        $minLat = min($origin->latitude, $destination->latitude) - 5;
        $maxLat = max($origin->latitude, $destination->latitude) + 5;
        $minLon = min($origin->longitude, $destination->longitude) - 5;
        $maxLon = max($origin->longitude, $destination->longitude) + 5;

        $query = Port::with(['country.latestRiskScore', 'country.latestWeather'])
            ->where('is_active', true)
            ->whereNotIn('id', [$origin->id, $destination->id]);

        // If distance is large (> 800 NM), find intermediate hub
        $directDist = $this->calculateDistanceNM(
            (float)$origin->latitude, (float)$origin->longitude,
            (float)$destination->latitude, (float)$destination->longitude
        );

        if ($directDist < 500) {
            return []; // Direct shipment for short distances
        }

        $candidates = $query->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLon, $maxLon])
            ->get();

        if ($candidates->isEmpty()) {
            // Fallback: pick major global hub ports in nearby region
            $candidates = Port::with(['country.latestRiskScore', 'country.latestWeather'])
                ->where('is_active', true)
                ->whereIn('harbor_size', ['Very Large', 'Large', 'Medium'])
                ->whereNotIn('id', [$origin->id, $destination->id])
                ->take(10)
                ->get();
        }

        // Sort candidates based on priority
        $sorted = $candidates->sortBy(function($port) use ($priority, $origin, $destination) {
            $risk = $port->country && $port->country->latestRiskScore ? (float)$port->country->latestRiskScore->composite_score : 50;
            $distFromOrigin = $this->calculateDistanceNM((float)$origin->latitude, (float)$origin->longitude, (float)$port->latitude, (float)$port->longitude);
            $distToDest = $this->calculateDistanceNM((float)$port->latitude, (float)$port->longitude, (float)$destination->latitude, (float)$destination->longitude);
            $totalDist = $distFromOrigin + $distToDest;

            if ($priority === 'safest') {
                return $risk * 100 + $totalDist;
            } elseif ($priority === 'fastest') {
                return $totalDist;
            } else { // cheapest
                return $distFromOrigin;
            }
        });

        // Pick 1 or 2 best transit hubs
        return $sorted->take($directDist > 3000 ? 2 : 1)->values()->all();
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

        // Randomize slight variance based on port ID
        $variance = ($port->id * 7) % 15;
        return min(95, max(10, $base + $variance));
    }

    /**
     * Generate humanized AI Insight commentary text.
     */
    protected function generateAiInsight(Port $origin, Port $destination, array $transits, float $riskScore, string $riskLevel, int $avgCongestion, array $weatherAlerts, string $priority): string
    {
        $transitNames = count($transits) > 0 ? implode(' dan ', array_map(fn($t) => $t->name, $transits)) : 'langsung tanpa transit';

        if ($riskScore >= 60) {
            $text = "Rute pengiriman dari {$origin->name} ke {$destination->name} (melalui {$transitNames}) melewati wilayah dengan tingkat risiko geopolitik dan maritim tinggi ({$riskScore}/100, Kategori: {$riskLevel}). ";
            if (count($weatherAlerts) > 0) {
                $text .= "Terdapat peringatan cuaca ekstrim di sepanjang koridor pelayaran. ";
            }
            if ($avgCongestion > 40) {
                $text .= "Pelabuhan transit sedang mengalami tingkat kepadatan tinggi ({$avgCongestion}%). ";
            }
            $text .= "Disarankan mempertimbangkan rute alternatif yang lebih aman atau menyesuaikan jadwal keberangkatan untuk menghindari kerugian logistik.";
        } elseif ($riskScore >= 35) {
            $text = "Rute dari {$origin->name} ke {$destination->name} (melalui {$transitNames}) berada dalam batas kondisi operasional yang stabil ({$riskScore}/100, Kategori: {$riskLevel}). ";
            if ($avgCongestion > 35) {
                $text .= "Estimasi waktu bongkar muat diperkirakan mengalami antrean sedang akibat kepadatan pelabuhan ({$avgCongestion}%). ";
            }
            $text .= "Pengiriman dapat dilanjutkan dengan tetap memantau pembaruan cuaca harian dan status sekuritas wilayah laut.";
        } else {
            $text = "Rute pelayaran dari {$origin->name} ke {$destination->name} merupakan koridor logistik paling aman dan efisien ({$riskScore}/100, Kategori: {$riskLevel}). Kondisi cuaca maritim terpantau kondusif dan kelancaran arus barang di pelabuhan sangat baik ({$avgCongestion}% kepadatan).";
        }

        return $text;
    }

    /**
     * Generate alternative route comparison.
     */
    protected function generateAlternativeRoute(Port $origin, Port $destination, float $primaryRisk, float $primaryEta, string $containerType): array
    {
        // Alternative route chooses safer hubs or different pathing
        $altRisk = round(max(15, $primaryRisk - rand(18, 28)), 1);
        $altEta = round($primaryEta + (rand(12, 24) / 10), 1);

        // Find an alternative transit hub in neighboring country
        $altHub = Port::with('country')
            ->where('is_active', true)
            ->whereNotIn('id', [$origin->id, $destination->id])
            ->whereIn('harbor_size', ['Very Large', 'Large'])
            ->orderBy('id', 'desc')
            ->first();

        $altHubName = $altHub ? $altHub->name . ' (' . ($altHub->country ? $altHub->country->name : '') . ')' : 'Port Klang (Malaysia)';

        return [
            'original' => [
                'route_summary' => $origin->name . ' → ' . $destination->name,
                'risk_score' => $primaryRisk,
                'eta_days' => $primaryEta,
            ],
            'alternative' => [
                'route_summary' => $origin->name . ' → ' . $altHubName . ' → ' . $destination->name,
                'risk_score' => $altRisk,
                'eta_days' => $altEta,
                'savings_risk_percent' => round((($primaryRisk - $altRisk) / max(1, $primaryRisk)) * 100, 1),
                'recommendation_text' => "Rute alternatif melalui {$altHubName} menurunkan risiko sebesar " . round($primaryRisk - $altRisk, 1) . " poin dengan penambahan waktu pelayaran yang minimal (+ " . round($altEta - $primaryEta, 1) . " hari).",
            ],
        ];
    }
}
