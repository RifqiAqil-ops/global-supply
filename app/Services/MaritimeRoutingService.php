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

        $directDist = $this->calculateDistanceNM($originLat, $originLon, $destLat, $destLon);

        // If short distance (< 250 NM), direct sea leg
        if ($directDist < 250) {
            $directCoords = $this->interpolateLeg([$originLat, $originLon], [$destLat, $destLon], 10);
            return [
                'coordinates' => $directCoords,
                'waypoints' => [],
                'chokepoints' => [],
                'warnings' => [],
                'total_distance_nm' => $directDist,
            ];
        }

        // Find entry and exit sea waypoints
        $startWpId = $this->findClosestWaypointId($originLat, $originLon, $avoidWaypointIds);
        $endWpId = $this->findClosestWaypointId($destLat, $destLon, $avoidWaypointIds);

        // Run Dijkstra pathfinder on sea graph
        $pathWpIds = $this->dijkstraPath($startWpId, $endWpId, $graph, $allWaypoints, $avoidWaypointIds);

        // Build full node sequence: [Origin -> Waypoint1 -> Waypoint2 ... -> Destination]
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

        // Generate smooth multi-point coordinates polyline
        $polyCoords = [];
        $totalDistanceNM = 0;

        for ($i = 0; $i < count($nodeSequence) - 1; $i++) {
            $p1 = [$nodeSequence[$i]['lat'], $nodeSequence[$i]['lng']];
            $p2 = [$nodeSequence[$i + 1]['lat'], $nodeSequence[$i + 1]['lng']];

            $legDist = $this->calculateDistanceNM($p1[0], $p1[1], $p2[0], $p2[1]);
            $totalDistanceNM += $legDist;

            // Interpolate points for smooth curve
            $steps = max(5, (int)round($legDist / 40));
            $legPoints = $this->interpolateLeg($p1, $p2, $steps);

            if ($i > 0) {
                array_shift($legPoints); // Avoid duplicate point at joint
            }
            foreach ($legPoints as $pt) {
                $polyCoords[] = $pt;
            }
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
     * Interpolate intermediate points for smooth curve navigation.
     */
    protected function interpolateLeg(array $p1, array $p2, int $steps = 8): array
    {
        $points = [];
        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $lat = $p1[0] + ($p2[0] - $p1[0]) * $t;
            $lng = $p1[1] + ($p2[1] - $p1[1]) * $t;
            $points[] = [round($lat, 5), round($lng, 5)];
        }
        return $points;
    }
}
