<?php

namespace App\Services\Repositories;

class MaritimeWaypointRepository
{
    /**
     * Get all curated global maritime sea waypoints & chokepoints.
     * Each waypoint has: id, name, lat, lng, type, warning (optional).
     */
    public function getWaypoints(): array
    {
        return [
            // --- Southeast Asia & East Asia ---
            'SG_STRAIT' => [
                'id' => 'SG_STRAIT',
                'name' => 'Strait of Malacca (Singapore Gate)',
                'lat' => 1.25,
                'lng' => 103.80,
                'type' => 'Chokepoint',
                'country' => 'Singapore / Malaysia',
                'warning' => 'Heavy Maritime Traffic & Coastal Congestion',
            ],
            'MALACCA_NORTH' => [
                'id' => 'MALACCA_NORTH',
                'name' => 'North Malacca Strait',
                'lat' => 5.2,
                'lng' => 99.5,
                'type' => 'Strait',
                'country' => 'Malaysia / Indonesia',
            ],
            'SUNDA_STRAIT' => [
                'id' => 'SUNDA_STRAIT',
                'name' => 'Sunda Strait Gate',
                'lat' => -5.9,
                'lng' => 105.8,
                'type' => 'Strait',
                'country' => 'Indonesia',
            ],
            'LOMBOK_STRAIT' => [
                'id' => 'LOMBOK_STRAIT',
                'name' => 'Lombok Strait Gate',
                'lat' => -8.7,
                'lng' => 115.7,
                'type' => 'Strait',
                'country' => 'Indonesia',
            ],
            'SCS_SOUTH' => [
                'id' => 'SCS_SOUTH',
                'name' => 'South China Sea Basin',
                'lat' => 4.0,
                'lng' => 108.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'SCS_NORTH' => [
                'id' => 'SCS_NORTH',
                'name' => 'Paracel Sea Passage',
                'lat' => 18.0,
                'lng' => 114.5,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'TAIWAN_STRAIT' => [
                'id' => 'TAIWAN_STRAIT',
                'name' => 'Taiwan Strait Passage',
                'lat' => 24.0,
                'lng' => 119.5,
                'type' => 'Chokepoint',
                'country' => 'Taiwan / China',
                'warning' => 'Geopolitical Surveillance & Naval Patrol Zone',
            ],
            'ECS_SHANGHAI' => [
                'id' => 'ECS_SHANGHAI',
                'name' => 'East China Sea Approach',
                'lat' => 30.5,
                'lng' => 122.8,
                'type' => 'Ocean Basin',
                'country' => 'China',
            ],
            'YELLOW_SEA' => [
                'id' => 'YELLOW_SEA',
                'name' => 'Yellow Sea Shipping Lane',
                'lat' => 36.0,
                'lng' => 123.0,
                'type' => 'Ocean Basin',
                'country' => 'China / Korea',
            ],
            'TSUSHIMA' => [
                'id' => 'TSUSHIMA',
                'name' => 'Tsushima Strait Gate',
                'lat' => 34.0,
                'lng' => 129.5,
                'type' => 'Strait',
                'country' => 'Japan / Korea',
            ],
            'TSUGARU' => [
                'id' => 'TSUGARU',
                'name' => 'Tsugaru Strait',
                'lat' => 41.5,
                'lng' => 140.5,
                'type' => 'Strait',
                'country' => 'Japan',
            ],

            // --- Indian Ocean & Middle East ---
            'ANDAMAN_SEA' => [
                'id' => 'ANDAMAN_SEA',
                'name' => 'Andaman Sea Gate',
                'lat' => 6.0,
                'lng' => 95.0,
                'type' => 'Ocean Basin',
                'country' => 'India / Indonesia',
            ],
            'BAY_OF_BENGAL' => [
                'id' => 'BAY_OF_BENGAL',
                'name' => 'Bay of Bengal Basin',
                'lat' => 12.0,
                'lng' => 88.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
                'warning' => 'Seasonal Monsoon & Cyclone Activity',
            ],
            'SRI_LANKA' => [
                'id' => 'SRI_LANKA',
                'name' => 'Colombo Deep Sea Passage',
                'lat' => 5.8,
                'lng' => 80.2,
                'type' => 'Transit Hub',
                'country' => 'Sri Lanka',
            ],
            'ARABIAN_SEA' => [
                'id' => 'ARABIAN_SEA',
                'name' => 'Arabian Sea Basin',
                'lat' => 14.0,
                'lng' => 64.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'HORMUZ_STRAIT' => [
                'id' => 'HORMUZ_STRAIT',
                'name' => 'Strait of Hormuz',
                'lat' => 26.3,
                'lng' => 56.4,
                'type' => 'Chokepoint',
                'country' => 'Oman / Iran',
                'warning' => 'High Geopolitical Tension & Tanker Escort Zone',
            ],
            'BAB_EL_MANDEB' => [
                'id' => 'BAB_EL_MANDEB',
                'name' => 'Bab el-Mandeb Strait',
                'lat' => 12.6,
                'lng' => 43.3,
                'type' => 'Chokepoint',
                'country' => 'Yemen / Djibouti',
                'warning' => 'Critical Geopolitical Threat & Drone Escort Area',
            ],
            'RED_SEA_MID' => [
                'id' => 'RED_SEA_MID',
                'name' => 'Red Sea Shipping Corridor',
                'lat' => 20.0,
                'lng' => 38.5,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
                'warning' => 'Active Maritime Security Advisory',
            ],
            'SUEZ_SOUTH' => [
                'id' => 'SUEZ_SOUTH',
                'name' => 'Gulf of Suez Approach',
                'lat' => 27.5,
                'lng' => 34.0,
                'type' => 'Strait',
                'country' => 'Egypt',
            ],
            'SUEZ_CANAL' => [
                'id' => 'SUEZ_CANAL',
                'name' => 'Suez Canal Chokepoint',
                'lat' => 29.9,
                'lng' => 32.5,
                'type' => 'Canal',
                'country' => 'Egypt',
                'warning' => 'Canal Vessel Queueing & Delayed Transit',
            ],

            // --- Mediterranean & Europe ---
            'MED_EAST' => [
                'id' => 'MED_EAST',
                'name' => 'Levantine Sea Waypoint',
                'lat' => 33.5,
                'lng' => 33.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'MED_CENTRAL' => [
                'id' => 'MED_CENTRAL',
                'name' => 'Central Mediterranean Basin',
                'lat' => 36.0,
                'lng' => 16.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'GIBRALTAR' => [
                'id' => 'GIBRALTAR',
                'name' => 'Strait of Gibraltar',
                'lat' => 35.9,
                'lng' => -5.3,
                'type' => 'Chokepoint',
                'country' => 'Spain / Morocco',
            ],
            'ATLANTIC_EU' => [
                'id' => 'ATLANTIC_EU',
                'name' => 'Bay of Biscay Approach',
                'lat' => 45.0,
                'lng' => -7.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'ENGLISH_CHANNEL' => [
                'id' => 'ENGLISH_CHANNEL',
                'name' => 'English Channel Passage',
                'lat' => 50.0,
                'lng' => -1.5,
                'type' => 'Chokepoint',
                'country' => 'UK / France',
                'warning' => 'Dense Shipping Traffic & Reduced Visibility Fog',
            ],
            'NORTH_SEA' => [
                'id' => 'NORTH_SEA',
                'name' => 'North Sea Shipping Lane (Rotterdam)',
                'lat' => 53.5,
                'lng' => 4.0,
                'type' => 'Ocean Basin',
                'country' => 'Netherlands',
            ],

            // --- Cape of Good Hope Alternative Bypass (Africa) ---
            'HORN_OF_AFRICA' => [
                'id' => 'HORN_OF_AFRICA',
                'name' => 'Cape Guardafui Bypass',
                'lat' => 11.8,
                'lng' => 51.5,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'MOZAMBIQUE_CHANNEL' => [
                'id' => 'MOZAMBIQUE_CHANNEL',
                'name' => 'Mozambique Channel Passage',
                'lat' => -18.0,
                'lng' => 41.0,
                'type' => 'Ocean Basin',
                'country' => 'Madagascar / Mozambique',
            ],
            'CAPE_GOOD_HOPE' => [
                'id' => 'CAPE_GOOD_HOPE',
                'name' => 'Cape of Good Hope Bypass',
                'lat' => -34.8,
                'lng' => 19.5,
                'type' => 'Chokepoint',
                'country' => 'South Africa',
                'warning' => 'Safe Deep Sea Bypass - Strong Ocean Swells',
            ],
            'SOUTH_ATLANTIC' => [
                'id' => 'SOUTH_ATLANTIC',
                'name' => 'South Atlantic Ocean Lane',
                'lat' => -15.0,
                'lng' => -5.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'GULF_OF_GUINEA' => [
                'id' => 'GULF_OF_GUINEA',
                'name' => 'West Africa Ocean Gate',
                'lat' => 4.0,
                'lng' => 2.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],

            // --- Americas & Pacific Ocean ---
            'PANAMA_CANAL' => [
                'id' => 'PANAMA_CANAL',
                'name' => 'Panama Canal Locks',
                'lat' => 8.9,
                'lng' => -79.5,
                'type' => 'Canal',
                'country' => 'Panama',
                'warning' => 'Drought Vessel Draft Restrictions',
            ],
            'CARIBBEAN_SEA' => [
                'id' => 'CARIBBEAN_SEA',
                'name' => 'Caribbean Sea Passage',
                'lat' => 15.0,
                'lng' => -73.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'US_EAST_COAST' => [
                'id' => 'US_EAST_COAST',
                'name' => 'US Atlantic Shipping Lane',
                'lat' => 38.0,
                'lng' => -73.0,
                'type' => 'Ocean Basin',
                'country' => 'United States',
            ],
            'PACIFIC_MID' => [
                'id' => 'PACIFIC_MID',
                'name' => 'Mid-Pacific Deep Water Lane',
                'lat' => 20.0,
                'lng' => 180.0,
                'type' => 'Ocean Basin',
                'country' => 'International Waters',
            ],
            'US_WEST_COAST' => [
                'id' => 'US_WEST_COAST',
                'name' => 'US Pacific Shipping Lane (LA)',
                'lat' => 33.0,
                'lng' => -118.5,
                'type' => 'Ocean Basin',
                'country' => 'United States',
            ],
        ];
    }

    /**
     * Get sea lane graph adjacency list connecting waypoints.
     */
    public function getGraphEdges(): array
    {
        return [
            // SE Asia Connections
            'SG_STRAIT' => ['MALACCA_NORTH', 'SUNDA_STRAIT', 'LOMBOK_STRAIT', 'SCS_SOUTH', 'ANDAMAN_SEA'],
            'MALACCA_NORTH' => ['SG_STRAIT', 'ANDAMAN_SEA'],
            'SUNDA_STRAIT' => ['SG_STRAIT', 'SRI_LANKA', 'CAPE_GOOD_HOPE'],
            'LOMBOK_STRAIT' => ['SG_STRAIT', 'CAPE_GOOD_HOPE'],

            // SCS & East Asia
            'SCS_SOUTH' => ['SG_STRAIT', 'SCS_NORTH'],
            'SCS_NORTH' => ['SCS_SOUTH', 'TAIWAN_STRAIT', 'ECS_SHANGHAI'],
            'TAIWAN_STRAIT' => ['SCS_NORTH', 'ECS_SHANGHAI', 'TSUSHIMA'],
            'ECS_SHANGHAI' => ['TAIWAN_STRAIT', 'YELLOW_SEA', 'TSUSHIMA', 'PACIFIC_MID'],
            'YELLOW_SEA' => ['ECS_SHANGHAI', 'TSUSHIMA'],
            'TSUSHIMA' => ['TAIWAN_STRAIT', 'ECS_SHANGHAI', 'TSUGARU'],
            'TSUGARU' => ['TSUSHIMA', 'PACIFIC_MID'],

            // Indian Ocean & Red Sea
            'ANDAMAN_SEA' => ['MALACCA_NORTH', 'SG_STRAIT', 'BAY_OF_BENGAL', 'SRI_LANKA'],
            'BAY_OF_BENGAL' => ['ANDAMAN_SEA', 'SRI_LANKA'],
            'SRI_LANKA' => ['ANDAMAN_SEA', 'BAY_OF_BENGAL', 'ARABIAN_SEA', 'BAB_EL_MANDEB', 'CAPE_GOOD_HOPE'],
            'ARABIAN_SEA' => ['SRI_LANKA', 'HORMUZ_STRAIT', 'BAB_EL_MANDEB'],
            'HORMUZ_STRAIT' => ['ARABIAN_SEA'],
            'BAB_EL_MANDEB' => ['SRI_LANKA', 'ARABIAN_SEA', 'RED_SEA_MID', 'HORN_OF_AFRICA'],
            'RED_SEA_MID' => ['BAB_EL_MANDEB', 'SUEZ_SOUTH'],
            'SUEZ_SOUTH' => ['RED_SEA_MID', 'SUEZ_CANAL'],
            'SUEZ_CANAL' => ['SUEZ_SOUTH', 'MED_EAST'],

            // Europe & Med
            'MED_EAST' => ['SUEZ_CANAL', 'MED_CENTRAL'],
            'MED_CENTRAL' => ['MED_EAST', 'GIBRALTAR'],
            'GIBRALTAR' => ['MED_CENTRAL', 'ATLANTIC_EU', 'GULF_OF_GUINEA'],
            'ATLANTIC_EU' => ['GIBRALTAR', 'ENGLISH_CHANNEL', 'CARIBBEAN_SEA'],
            'ENGLISH_CHANNEL' => ['ATLANTIC_EU', 'NORTH_SEA'],
            'NORTH_SEA' => ['ENGLISH_CHANNEL', 'US_EAST_COAST'],

            // Africa Cape Bypass
            'HORN_OF_AFRICA' => ['BAB_EL_MANDEB', 'MOZAMBIQUE_CHANNEL', 'SRI_LANKA'],
            'MOZAMBIQUE_CHANNEL' => ['HORN_OF_AFRICA', 'CAPE_GOOD_HOPE'],
            'CAPE_GOOD_HOPE' => ['SRI_LANKA', 'SUNDA_STRAIT', 'MOZAMBIQUE_CHANNEL', 'SOUTH_ATLANTIC'],
            'SOUTH_ATLANTIC' => ['CAPE_GOOD_HOPE', 'GULF_OF_GUINEA'],
            'GULF_OF_GUINEA' => ['SOUTH_ATLANTIC', 'GIBRALTAR', 'CARIBBEAN_SEA'],

            // Pacific & Americas
            'PACIFIC_MID' => ['ECS_SHANGHAI', 'TSUGARU', 'US_WEST_COAST', 'PANAMA_CANAL'],
            'US_WEST_COAST' => ['PACIFIC_MID', 'PANAMA_CANAL'],
            'PANAMA_CANAL' => ['US_WEST_COAST', 'PACIFIC_MID', 'CARIBBEAN_SEA'],
            'CARIBBEAN_SEA' => ['PANAMA_CANAL', 'US_EAST_COAST', 'ATLANTIC_EU'],
            'US_EAST_COAST' => ['CARIBBEAN_SEA', 'NORTH_SEA', 'ATLANTIC_EU'],
        ];
    }
}
