<?php

namespace Database\Seeders;

use App\Models\SystemConfig;
use Illuminate\Database\Seeder;

class SystemConfigSeeder extends Seeder
{
    /**
     * Seed the default system configurations.
     */
    public function run(): void
    {
        $configs = [
            // Cache Settings
            [
                'key' => 'cache_duration_weather',
                'value' => '60',
                'type' => 'integer',
                'group' => 'cache',
                'label' => 'Weather Cache Duration (minutes)',
                'description' => 'How long weather data is cached before refreshing from API.',
                'is_editable' => true,
            ],
            [
                'key' => 'cache_duration_economic',
                'value' => '1440',
                'type' => 'integer',
                'group' => 'cache',
                'label' => 'Economic Data Cache Duration (minutes)',
                'description' => 'How long economic indicator data is cached.',
                'is_editable' => true,
            ],
            [
                'key' => 'cache_duration_exchange_rate',
                'value' => '240',
                'type' => 'integer',
                'group' => 'cache',
                'label' => 'Exchange Rate Cache Duration (minutes)',
                'description' => 'How long exchange rate data is cached.',
                'is_editable' => true,
            ],
            [
                'key' => 'cache_duration_news',
                'value' => '30',
                'type' => 'integer',
                'group' => 'cache',
                'label' => 'News Cache Duration (minutes)',
                'description' => 'How long news articles are cached.',
                'is_editable' => true,
            ],

            // Risk Settings
            [
                'key' => 'risk_score_low_max',
                'value' => '25',
                'type' => 'integer',
                'group' => 'risk',
                'label' => 'Low Risk Maximum Score',
                'description' => 'Maximum score for Low risk classification.',
                'is_editable' => true,
            ],
            [
                'key' => 'risk_score_medium_max',
                'value' => '50',
                'type' => 'integer',
                'group' => 'risk',
                'label' => 'Medium Risk Maximum Score',
                'description' => 'Maximum score for Medium risk classification.',
                'is_editable' => true,
            ],
            [
                'key' => 'risk_score_high_max',
                'value' => '75',
                'type' => 'integer',
                'group' => 'risk',
                'label' => 'High Risk Maximum Score',
                'description' => 'Maximum score for High risk classification.',
                'is_editable' => true,
            ],

            // Display Settings
            [
                'key' => 'items_per_page_default',
                'value' => '25',
                'type' => 'integer',
                'group' => 'display',
                'label' => 'Default Items Per Page',
                'description' => 'Default pagination count for list views.',
                'is_editable' => true,
            ],
            [
                'key' => 'max_comparison_countries',
                'value' => '4',
                'type' => 'integer',
                'group' => 'comparison',
                'label' => 'Max Comparison Countries',
                'description' => 'Maximum number of countries that can be compared side-by-side.',
                'is_editable' => true,
            ],

            // News Settings
            [
                'key' => 'news_fetch_limit',
                'value' => '10',
                'type' => 'integer',
                'group' => 'news',
                'label' => 'News Fetch Limit',
                'description' => 'Number of news articles to fetch per API call.',
                'is_editable' => true,
            ],

            // API Settings
            [
                'key' => 'api_timeout_seconds',
                'value' => '10',
                'type' => 'integer',
                'group' => 'api',
                'label' => 'API Timeout (seconds)',
                'description' => 'Maximum wait time for external API responses.',
                'is_editable' => true,
            ],
            [
                'key' => 'api_retry_attempts',
                'value' => '3',
                'type' => 'integer',
                'group' => 'api',
                'label' => 'API Retry Attempts',
                'description' => 'Number of retry attempts for failed API calls.',
                'is_editable' => true,
            ],

            // App Settings
            [
                'key' => 'app_maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'app',
                'label' => 'Maintenance Mode',
                'description' => 'Enable or disable maintenance mode for the application.',
                'is_editable' => true,
            ],
            [
                'key' => 'data_retention_months',
                'value' => '12',
                'type' => 'integer',
                'group' => 'app',
                'label' => 'Data Retention Period (months)',
                'description' => 'How long historical data is retained before archival.',
                'is_editable' => true,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfig::updateOrCreate(
                ['key' => $config['key']],
                $config
            );
        }
    }
}
