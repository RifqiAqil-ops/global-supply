<?php

namespace App\Services;

use App\Services\Repositories\MaritimeWaypointRepository;

class MaritimeRoutingService
{
    protected MaritimeWaypointRepository $repository;

    public function __construct(MaritimeWaypointRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Calculate Nautical Miles distance using Haversine formula.
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

        return round($km / 1.852, 1);
    }

    /**
     * Find nearest sea waypoint ID for a given coordinate.
     */
    public function findClosestWaypointId(float $lat, float $lon, array $exclude = []): string
    {
        $waypoints = $this->repository->getWaypoints();
        $closestId = 'SG_STRAIT';
        $minDist = 999999;

        foreach ($waypoints as $id => $wp) {
            if (in_array($id, $exclude)) continue;
            $dist = $this->calculateDistanceNM($lat, $lon, $wp['lat'], $wp['lng']);
            if ($dist < $minDist) {
                $minDist = $dist;
                $closestId = $id;
            }
        }

        return $closestId;
    }

    /**
     * Solve sea route between origin and destination coordinates.
     */
    public function calculateSeaRoute(float $originLat, float $originLon, float $destLat, float $destLon, array $avoidWaypointIds = []): array
    {
        $allWaypoints = $this->repository->getWaypoints();
        $graph = $this->repository->getGraphEdges();

        // Find entry and exit sea waypoints
        $startWpId = $this->findClosestWaypointId($originLat, $originLon, $avoidWaypointIds);
        $endWpId = $this->findClosestWaypointId($destLat, $destLon, $avoidWaypointIds);

        // Run Dijkstra pathfinder on sea graph
        $pathWpIds = $this->dijkstraPath($startWpId, $endWpId, $graph, $allWaypoints, $avoidWaypointIds);

        // Build full node sequence: [Origin -> Sea Waypoint 1 -> Sea Waypoint 2 ... -> Destination]
        $nodeSequence = [
            ['lat' => $originLat, 'lng' => $originLon, 'name' => 'Origin Port', 'type' => 'Port']
        ];

        $transitWaypoints = [];
        $chokepoints = [];
        $warnings = [];

        foreach ($pathWpIds as $wpId) {
            if (isset($allWaypoints[$wpId])) {
                $wp = $allWaypoints[$wpId];
                $nodeSequence[] = $wp;
                $transitWaypoints[] = $wp;
                
                if ($wp['type'] === 'Chokepoint' || $wp['type'] === 'Canal') {
                    $chokepoints[] = $wp;
                }
                if (!empty($wp['warning'])) {
                    $warnings[] = [
                        'waypoint_id' => $wp['id'],
                        'name' => $wp['name'],
                        'warning' => $wp['warning'],
                        'lat' => $wp['lat'],
                        'lng' => $wp['lng'],
                    ];
                }
            }
        }

        $nodeSequence[] = ['lat' => $destLat, 'lng' => $destLon, 'name' => 'Destination Port', 'type' => 'Port'];

        // Generate smooth Catmull-Rom curved polyline strictly through sea waypoints
        $polyCoords = $this->generateCatmullRomSpline($nodeSequence, 12);
        
        // Calculate total sea distance
        $totalDistanceNM = 0;
        for ($i = 0; $i < count($nodeSequence) - 1; $i++) {
            $totalDistanceNM += $this->calculateDistanceNM(
                $nodeSequence[$i]['lat'], $nodeSequence[$i]['lng'],
                $nodeSequence[$i + 1]['lat'], $nodeSequence[$i + 1]['lng']
            );
        }

        return [
            'coordinates' => $polyCoords,
            'waypoints' => $transitWaypoints,
            'chokepoints' => $chokepoints,
            'warnings' => $warnings,
            'total_distance_nm' => round($totalDistanceNM, 1),
        ];
    }

    /**
     * Dijkstra Shortest Path algorithm across sea lane graph.
     */
    protected function dijkstraPath(string $start, string $target, array $graph, array $waypoints, array $avoid = []): array
    {
        if ($start === $target) {
            return [$start];
        }

        $distances = [];
        $previous = [];
        $nodes = new \SplPriorityQueue();

        foreach (array_keys($waypoints) as $nodeId) {
            if (in_array($nodeId, $avoid)) continue;
            if ($nodeId === $start) {
                $distances[$nodeId] = 0;
                $nodes->insert($nodeId, 0);
            } else {
                $distances[$nodeId] = INF;
                $nodes->insert($nodeId, -INF);
            }
            $previous[$nodeId] = null;
        }

        while (!$nodes->isEmpty()) {
            $current = $nodes->extract();
            if ($current === $target) {
                break;
            }

            if (!isset($distances[$current]) || $distances[$current] === INF) {
                continue;
            }

            $neighbors = $graph[$current] ?? [];
            foreach ($neighbors as $neighbor) {
                if (in_array($neighbor, $avoid)) continue;
                if (!isset($waypoints[$neighbor])) continue;

                $w1 = $waypoints[$current];
                $w2 = $waypoints[$neighbor];
                
                $edgeWeight = $this->calculateDistanceNM($w1['lat'], $w1['lng'], $w2['lat'], $w2['lng']);
                $alt = $distances[$current] + $edgeWeight;

                if ($alt < ($distances[$neighbor] ?? INF)) {
                    $distances[$neighbor] = $alt;
                    $previous[$neighbor] = $current;
                    $nodes->insert($neighbor, -$alt);
                }
            }
        }

        $path = [];
        $curr = $target;
        while ($curr !== null) {
            array_unshift($path, $curr);
            $curr = $previous[$curr] ?? null;
        }

        return $path[0] === $start ? $path : [$start, $target];
    }

    /**
     * Generate Catmull-Rom spline curves across sea waypoints.
     */
    protected function generateCatmullRomSpline(array $nodes, int $samplesPerSegment = 12): array
    {
        $count = count($nodes);
        if ($count < 2) {
            return array_map(fn($n) => [$n['lat'], $n['lng']], $nodes);
        }

        $pts = array_map(fn($n) => [$n['lat'], $n['lng']], $nodes);
        $result = [];

        for ($i = 0; $i < $count - 1; $i++) {
            $p0 = $pts[max(0, $i - 1)];
            $p1 = $pts[$i];
            $p2 = $pts[$i + 1];
            $p3 = $pts[min($count - 1, $i + 2)];

            for ($j = 0; $j < $samplesPerSegment; $j++) {
                if ($i > 0 && $j === 0) continue; // Avoid duplicate point at joint

                $t = $j / $samplesPerSegment;
                $t2 = $t * $t;
                $t3 = $t2 * $t;

                $f0 = -0.5 * $t3 + $t2 - 0.5 * $t;
                $f1 = 1.5 * $t3 - 2.5 * $t2 + 1.0;
                $f2 = -1.5 * $t3 + 2.0 * $t2 + 0.5 * $t;
                $f3 = 0.5 * $t3 - 0.5 * $t2;

                $lat = $p0[0] * $f0 + $p1[0] * $f1 + $p2[0] * $f2 + $p3[0] * $f3;
                $lng = $p0[1] * $f0 + $p1[1] * $f1 + $p2[1] * $f2 + $p3[1] * $f3;

                $result[] = [round($lat, 5), round($lng, 5)];
            }
        }

        // Always append exact final destination point
        $lastPt = $pts[$count - 1];
        $result[] = [round($lastPt[0], 5), round($lastPt[1], 5)];

        return $result;
    }
}
