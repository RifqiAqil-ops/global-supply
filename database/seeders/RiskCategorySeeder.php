<?php

namespace Database\Seeders;

use App\Models\RiskCategory;
use Illuminate\Database\Seeder;

class RiskCategorySeeder extends Seeder
{
    /**
     * Seed the risk categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Economic Risk',
                'slug' => 'economic-risk',
                'description' => 'Risk based on economic indicators such as GDP growth, inflation rate, unemployment, and trade balance.',
                'icon' => 'bi-graph-down',
                'color' => '#dc3545',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Weather Risk',
                'slug' => 'weather-risk',
                'description' => 'Risk based on weather conditions including extreme weather events, natural disasters, and climate patterns.',
                'icon' => 'bi-cloud-lightning',
                'color' => '#fd7e14',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Geopolitical Risk',
                'slug' => 'geopolitical-risk',
                'description' => 'Risk based on political stability, conflicts, sanctions, trade wars, and government policy changes.',
                'icon' => 'bi-shield-exclamation',
                'color' => '#6f42c1',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Logistics Risk',
                'slug' => 'logistics-risk',
                'description' => 'Risk based on port infrastructure, shipping routes, port congestion, and transportation reliability.',
                'icon' => 'bi-truck',
                'color' => '#0d6efd',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Currency Stability Risk',
                'slug' => 'currency-stability-risk',
                'description' => 'Risk based on currency volatility, exchange rate fluctuations, and monetary policy stability.',
                'icon' => 'bi-currency-exchange',
                'color' => '#198754',
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            RiskCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
