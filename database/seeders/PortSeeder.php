<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Seeder;

class PortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $portsData = [
            // Indonesia
            [
                'iso2' => 'ID',
                'name' => 'Tanjung Priok',
                'port_code' => 'IDTPP',
                'latitude' => -6.1030,
                'longitude' => 106.8790,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Breakwater',
                'shelter' => 'Good',
                'max_vessel_length' => 300,
                'max_depth' => 14.00,
                'facilities' => ['Dry Dock', 'Container Terminal', 'Bunkering'],
            ],
            [
                'iso2' => 'ID',
                'name' => 'Tanjung Perak',
                'port_code' => 'IDTPE',
                'latitude' => -7.2020,
                'longitude' => 112.7230,
                'port_type' => 'sea',
                'port_size' => 'medium',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 250,
                'max_depth' => 12.00,
                'facilities' => ['Container Terminal', 'Passenger Terminal'],
            ],
            [
                'iso2' => 'ID',
                'name' => 'Port of Belawan',
                'port_code' => 'IDBLW',
                'latitude' => 3.7830,
                'longitude' => 98.6830,
                'port_type' => 'river',
                'port_size' => 'medium',
                'harbor_type' => 'River Natural',
                'shelter' => 'Good',
                'max_vessel_length' => 200,
                'max_depth' => 10.00,
                'facilities' => ['Container Terminal', 'Bunkering'],
            ],
            [
                'iso2' => 'ID',
                'name' => 'Port of Makassar',
                'port_code' => 'IDMAK',
                'latitude' => -5.1270,
                'longitude' => 119.4060,
                'port_type' => 'sea',
                'port_size' => 'medium',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Good',
                'max_vessel_length' => 220,
                'max_depth' => 11.50,
                'facilities' => ['Container Terminal', 'General Cargo'],
            ],

            // United States
            [
                'iso2' => 'US',
                'name' => 'Port of Los Angeles',
                'port_code' => 'USLAX',
                'latitude' => 33.7290,
                'longitude' => -118.2620,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Breakwater',
                'shelter' => 'Excellent',
                'max_vessel_length' => 400,
                'max_depth' => 16.00,
                'facilities' => ['Dry Dock', 'Container Terminal', 'Bunkering', 'Rail Link'],
            ],
            [
                'iso2' => 'US',
                'name' => 'Port of Long Beach',
                'port_code' => 'USLGB',
                'latitude' => 33.7540,
                'longitude' => -118.2150,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Breakwater',
                'shelter' => 'Excellent',
                'max_vessel_length' => 400,
                'max_depth' => 16.00,
                'facilities' => ['Container Terminal', 'Bunkering', 'Rail Link'],
            ],
            [
                'iso2' => 'US',
                'name' => 'Port of New York & New Jersey',
                'port_code' => 'USNYC',
                'latitude' => 40.6700,
                'longitude' => -74.1300,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 366,
                'max_depth' => 15.00,
                'facilities' => ['Dry Dock', 'Container Terminal', 'Rail Link'],
            ],
            [
                'iso2' => 'US',
                'name' => 'Port of Seattle',
                'port_code' => 'USSEA',
                'latitude' => 47.6000,
                'longitude' => -122.3400,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 320,
                'max_depth' => 14.50,
                'facilities' => ['Container Terminal', 'Passenger Terminal'],
            ],
            [
                'iso2' => 'US',
                'name' => 'Port of Houston',
                'port_code' => 'USHOU',
                'latitude' => 29.7400,
                'longitude' => -95.0800,
                'port_type' => 'canal',
                'port_size' => 'large',
                'harbor_type' => 'Canal or Channel',
                'shelter' => 'Excellent',
                'max_vessel_length' => 300,
                'max_depth' => 13.70,
                'facilities' => ['Chemical Terminal', 'Container Terminal'],
            ],

            // Japan
            [
                'iso2' => 'JP',
                'name' => 'Port of Tokyo',
                'port_code' => 'JPTYO',
                'latitude' => 35.6270,
                'longitude' => 139.7990,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 350,
                'max_depth' => 15.00,
                'facilities' => ['Container Terminal', 'Passenger Terminal', 'Rail Link'],
            ],
            [
                'iso2' => 'JP',
                'name' => 'Port of Yokohama',
                'port_code' => 'JPYOK',
                'latitude' => 35.4500,
                'longitude' => 139.6670,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Breakwater',
                'shelter' => 'Excellent',
                'max_vessel_length' => 350,
                'max_depth' => 15.00,
                'facilities' => ['Dry Dock', 'Container Terminal', 'Bunkering'],
            ],
            [
                'iso2' => 'JP',
                'name' => 'Port of Kobe',
                'port_code' => 'JPKOB',
                'latitude' => 34.6800,
                'longitude' => 135.2200,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 320,
                'max_depth' => 14.00,
                'facilities' => ['Container Terminal', 'Passenger Terminal'],
            ],
            [
                'iso2' => 'JP',
                'name' => 'Port of Osaka',
                'port_code' => 'JPOSA',
                'latitude' => 34.6400,
                'longitude' => 135.4200,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 300,
                'max_depth' => 13.00,
                'facilities' => ['Container Terminal', 'Dry Dock'],
            ],

            // Singapore
            [
                'iso2' => 'SG',
                'name' => 'Port of Singapore',
                'port_code' => 'SGSIN',
                'latitude' => 1.2640,
                'longitude' => 103.8400,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 400,
                'max_depth' => 18.00,
                'facilities' => ['Dry Dock', 'Container Terminal', 'Bunkering', 'Ship Repair'],
            ],

            // Netherlands
            [
                'iso2' => 'NL',
                'name' => 'Port of Rotterdam',
                'port_code' => 'NLRTM',
                'latitude' => 51.9200,
                'longitude' => 4.1500,
                'port_type' => 'river',
                'port_size' => 'large',
                'harbor_type' => 'River Basin',
                'shelter' => 'Excellent',
                'max_vessel_length' => 400,
                'max_depth' => 20.00,
                'facilities' => ['Dry Dock', 'Container Terminal', 'Bunkering'],
            ],

            // Germany
            [
                'iso2' => 'DE',
                'name' => 'Port of Hamburg',
                'port_code' => 'DEHAM',
                'latitude' => 53.5300,
                'longitude' => 9.9500,
                'port_type' => 'river',
                'port_size' => 'large',
                'harbor_type' => 'River Basin',
                'shelter' => 'Excellent',
                'max_vessel_length' => 366,
                'max_depth' => 15.00,
                'facilities' => ['Dry Dock', 'Container Terminal', 'Bunkering'],
            ],

            // United Kingdom
            [
                'iso2' => 'GB',
                'name' => 'Port of London',
                'port_code' => 'GBLON',
                'latitude' => 51.5000,
                'longitude' => 0.0500,
                'port_type' => 'river',
                'port_size' => 'medium',
                'harbor_type' => 'River Natural',
                'shelter' => 'Good',
                'max_vessel_length' => 250,
                'max_depth' => 10.00,
                'facilities' => ['General Cargo', 'Bunkering'],
            ],

            // Australia
            [
                'iso2' => 'AU',
                'name' => 'Port of Melbourne',
                'port_code' => 'AUMEL',
                'latitude' => -37.8400,
                'longitude' => 144.9100,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 320,
                'max_depth' => 14.00,
                'facilities' => ['Container Terminal', 'Bunkering'],
            ],

            // Brazil
            [
                'iso2' => 'BR',
                'name' => 'Port of Santos',
                'port_code' => 'BRSSZ',
                'latitude' => -23.9500,
                'longitude' => -46.3000,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Good',
                'max_vessel_length' => 300,
                'max_depth' => 13.00,
                'facilities' => ['Container Terminal', 'Dry Dock'],
            ],

            // India
            [
                'iso2' => 'IN',
                'name' => 'Nhava Sheva (Jawaharlal Nehru Port)',
                'port_code' => 'INNSA',
                'latitude' => 18.9500,
                'longitude' => 72.9500,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 350,
                'max_depth' => 14.00,
                'facilities' => ['Container Terminal', 'Bunkering'],
            ],

            // South Africa
            [
                'iso2' => 'ZA',
                'name' => 'Port of Durban',
                'port_code' => 'ZADUR',
                'latitude' => -29.8700,
                'longitude' => 31.0200,
                'port_type' => 'sea',
                'port_size' => 'large',
                'harbor_type' => 'Coastal Natural',
                'shelter' => 'Excellent',
                'max_vessel_length' => 300,
                'max_depth' => 12.00,
                'facilities' => ['Dry Dock', 'Container Terminal'],
            ],
        ];

        foreach ($portsData as $p) {
            $country = Country::where('iso2', $p['iso2'])->first();
            if (!$country) {
                continue; // Skip if country doesn't exist
            }

            Port::updateOrCreate(
                ['port_code' => $p['port_code']],
                [
                    'country_id' => $country->id,
                    'name' => $p['name'],
                    'latitude' => $p['latitude'],
                    'longitude' => $p['longitude'],
                    'port_type' => $p['port_type'],
                    'port_size' => $p['port_size'],
                    'harbor_type' => $p['harbor_type'],
                    'shelter' => $p['shelter'],
                    'max_vessel_length' => $p['max_vessel_length'],
                    'max_depth' => $p['max_depth'],
                    'facilities' => $p['facilities'],
                    'is_active' => true,
                ]
            );
        }
    }
}
