<?php

namespace Database\Seeders;

use App\Models\RiskCategory;
use App\Models\RiskWeight;
use Illuminate\Database\Seeder;

class RiskWeightSeeder extends Seeder
{
    /**
     * Seed the default risk weights.
     */
    public function run(): void
    {
        $weights = [
            'economic-risk' => [
                'weight' => 0.2500,
                'description' => 'Economic stability contributes 25% to the overall risk score.',
            ],
            'weather-risk' => [
                'weight' => 0.1500,
                'description' => 'Weather conditions contribute 15% to the overall risk score.',
            ],
            'geopolitical-risk' => [
                'weight' => 0.2500,
                'description' => 'Geopolitical stability contributes 25% to the overall risk score.',
            ],
            'logistics-risk' => [
                'weight' => 0.1500,
                'description' => 'Logistics infrastructure contributes 15% to the overall risk score.',
            ],
            'currency-stability-risk' => [
                'weight' => 0.2000,
                'description' => 'Currency stability contributes 20% to the overall risk score.',
            ],
        ];

        foreach ($weights as $slug => $data) {
            $category = RiskCategory::where('slug', $slug)->first();

            if ($category) {
                RiskWeight::updateOrCreate(
                    ['risk_category_id' => $category->id],
                    [
                        'weight' => $data['weight'],
                        'min_threshold' => 0,
                        'max_threshold' => 100,
                        'description' => $data['description'],
                    ]
                );
            }
        }
    }
}
