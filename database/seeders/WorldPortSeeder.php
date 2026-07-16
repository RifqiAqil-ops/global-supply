<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Port;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WorldPortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Downloading World Port Index dataset (ports.json)...');
        
        $response = Http::timeout(60)->get('https://raw.githubusercontent.com/tayljordan/ports/main/ports.json');
        if (!$response->successful()) {
            $this->command->error('Failed to download ports.json from GitHub.');
            return;
        }

        $data = $response->json();
        $portsList = $data['ports'] ?? [];
        $total = count($portsList);
        $this->command->info("Loaded {$total} ports from dataset. Seeding...");

        // Load all countries lookup map by name and iso_code
        $countries = Country::all();
        $countriesMap = [];
        foreach ($countries as $c) {
            $countriesMap[strtolower($c->name)] = $c->id;
            // Also map ISO-2
            if ($c->iso_code) {
                $countriesMap[strtolower($c->iso_code)] = $c->id;
            }
        }

        $countryAlias = [
            'united states' => 'united states',
            'united states of america' => 'united states',
            'usa' => 'united states',
            'united kingdom' => 'united kingdom',
            'uk' => 'united kingdom',
            'great britain' => 'united kingdom',
            'viet nam' => 'vietnam',
            'korea, republic of' => 'south korea',
            'korea, democratic people\'s republic of' => 'north korea',
            'russian federation' => 'russia',
            'syrian arab republic' => 'syria',
            'iran, islamic republic of' => 'iran',
            'venezuela, bolivarian republic of' => 'venezuela',
            'bolivia, plurinational state of' => 'bolivia',
            'tanzania, united republic of' => 'tanzania',
            'congo, democratic republic of the' => 'congo (dem. rep.)',
            'cote d\'ivoire' => 'ivory coast',
            'côte d\'ivoire' => 'ivory coast',
            'brunei darussalam' => 'brunei',
            'syria' => 'syria',
            'vietnam' => 'vietnam',
            'russia' => 'russia',
        ];

        $batch = [];
        $batchSize = 250;
        $insertedCount = 0;
        $matchedCountries = [];

        foreach ($portsList as $p) {
            $portCountry = trim($p['country'] ?? '');
            if (empty($portCountry)) continue;

            $lookupName = strtolower($portCountry);
            if (isset($countryAlias[$lookupName])) {
                $lookupName = strtolower($countryAlias[$lookupName]);
            }

            $countryId = $countriesMap[$lookupName] ?? null;

            // If still not matched, try matching parts
            if (!$countryId) {
                foreach ($countriesMap as $name => $id) {
                    if (str_contains($name, $lookupName) || str_contains($lookupName, $name)) {
                        $countryId = $id;
                        break;
                    }
                }
            }

            // Only seed ports for which we have country records in the DB
            if ($countryId) {
                $matchedCountries[$countryId] = true;

                $batch[] = [
                    'country_id' => $countryId,
                    'name' => mb_substr(trim($p['wpi_port_name'] ?? 'Unnamed Port'), 0, 255),
                    'port_code' => mb_substr(trim($p['wpi_port_id'] ?? ''), 0, 20),
                    'un_locode' => mb_substr(trim($p['point_of_interest'] ?? ''), 0, 50),
                    'latitude' => (float) ($p['latitude'] ?? 0),
                    'longitude' => (float) ($p['longitude'] ?? 0),
                    'port_type' => 'sea',
                    'port_size' => mb_substr(trim($p['port_size'] ?? 'Small'), 0, 50),
                    'harbor_size' => mb_substr(trim($p['port_size'] ?? 'Small'), 0, 50),
                    'harbor_type' => mb_substr(trim($p['state'] ?? 'Coastal'), 0, 100),
                    'shelter' => null,
                    'max_vessel_length' => null,
                    'max_vessel_size' => mb_substr(trim($p['max_vessel_size'] ?? ''), 0, 100),
                    'max_depth' => empty($p['cargo_pier_depth_max_m']) ? null : (float) $p['cargo_pier_depth_max_m'],
                    'region' => mb_substr(trim($p['state'] ?? ''), 0, 100),
                    'facilities' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($batch) >= $batchSize) {
                    Port::insert($batch);
                    $insertedCount += count($batch);
                    $batch = [];
                }
            }
        }

        if (count($batch) > 0) {
            Port::insert($batch);
            $insertedCount += count($batch);
        }

        $this->command->info("Seeded {$insertedCount} ports across " . count($matchedCountries) . " countries.");
    }
}
