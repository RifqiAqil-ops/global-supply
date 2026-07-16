<?php

namespace Database\Seeders;

use App\Models\NewsArticle;
use App\Models\Country;
use Illuminate\Database\Seeder;

class NewsArticleSeeder extends Seeder
{
    public function run(): void
    {
        $countries = Country::all();
        $indonesia = Country::where('iso2', 'ID')->first();

        $news = [
            [
                'title' => 'Global Port Congestion Hits 2-Year High Amid Red Sea Delays',
                'description' => 'Container ship queues build up across global ports as vessels reroute around Africa, straining shipping lanes.',
                'content' => 'Global container shipping routes are facing unprecedented gridlocks, with major logistics centers reporting vessel queues not seen since the peak of the pandemic supply shocks.',
                'source_name' => 'Logistics Intelligence',
                'source_url' => 'https://example.com/port-congestion',
                'category' => 'logistics',
                'sentiment' => 'negative',
                'country_id' => null,
            ],
            [
                'title' => 'Trade Tariff Negotiations Update: High Stakes for Emerging Economies',
                'description' => 'A new round of multilateral tariff disputes threatens export projections for developing industrial regions.',
                'content' => 'Negotiators from major trading blocs are deadlocked on tariff levels for industrial goods, causing economic forecasters to downgrade manufacturing output projections.',
                'source_name' => 'Global Trade Review',
                'source_url' => 'https://example.com/tariff-negotiations',
                'category' => 'economic',
                'sentiment' => 'neutral',
                'country_id' => null,
            ],
            [
                'title' => 'Supply Chain Resilience Index Shows Moderate Improvement',
                'description' => 'Improved regional warehousing capacity has offset some shipping bottlenecks, boosting general supply reliability.',
                'content' => 'The quarterly global supply chain resilience tracker indicates a positive uptick, primarily driven by massive investments in domestic distribution infrastructure.',
                'source_name' => 'SaaS Logistics Today',
                'source_url' => 'https://example.com/resilience-index',
                'category' => 'logistics',
                'sentiment' => 'positive',
                'country_id' => null,
            ],
        ];

        foreach ($news as $n) {
            NewsArticle::updateOrCreate(
                ['title' => $n['title']],
                array_merge($n, [
                    'published_at' => now()->subHours(rand(1, 48)),
                    'fetched_at' => now(),
                ])
            );
        }

        // Specific mock news for countries to ensure detail page has news
        foreach ($countries as $c) {
            $countryNews = [
                [
                    'title' => "{$c->name} Trade Volume Outlook Strengthens for Second Half",
                    'description' => "Domestic shipping and import-export activity in {$c->name} registers positive indicators.",
                    'content' => "Economic forecasters have revised {$c->name}'s industrial export predictions upward, citing strong consumption patterns and reliable cargo turnarounds.",
                    'source_name' => 'Regional Logistics Portal',
                    'source_url' => "https://example.com/country-trade-{$c->iso2}",
                    'category' => 'economic',
                    'sentiment' => 'positive',
                    'country_id' => $c->id,
                ],
                [
                    'title' => "{$c->name} Adapts to Geopolitical Shift in Regional Supply Corridors",
                    'description' => "New policy frameworks in {$c->name} aim to mitigate potential transit shocks across nearby borders.",
                    'content' => "Customs authorities in {$c->name} are accelerating clearance processes to secure transit capacity and sustain supply integrity.",
                    'source_name' => 'Supply Chain Sentinel',
                    'source_url' => "https://example.com/country-geopolitics-{$c->iso2}",
                    'category' => 'geopolitical',
                    'sentiment' => 'neutral',
                    'country_id' => $c->id,
                ],
            ];

            foreach ($countryNews as $cn) {
                NewsArticle::updateOrCreate(
                    ['title' => $cn['title']],
                    array_merge($cn, [
                        'published_at' => now()->subHours(rand(1, 48)),
                        'fetched_at' => now(),
                    ])
                );
            }
        }
    }
}
