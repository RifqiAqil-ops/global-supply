<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        if (!$admin) return;

        $articles = [
            [
                'title' => 'Suez Canal Congestion & Geopolitical Risk Analysis',
                'summary' => 'This report analyzes the impact of sidetracked shipping corridors in the Red Sea and sidetracking routes via Cape of Good Hope.',
                'content' => 'Global logistics networks are facing increased transit delays and fuel costs due to ongoing security threats. This analysis compiles recent performance benchmarks for major container vessels rerouting around Africa.',
                'status' => 'published',
                'published_at' => now()->subDays(5)
            ],
            [
                'title' => 'Inflation Volatility Across Key Asian Manufacturing Hubs',
                'summary' => 'Review of consumer price indices and industrial production inflation parameters in Singapore, Vietnam, and Malaysia.',
                'content' => 'High energy inputs and rising commodity base prices have placed significant inflationary pressures on local manufacturers. We evaluate supply chain implications and price pass-through metrics.',
                'status' => 'published',
                'published_at' => now()->subDays(2)
            ]
        ];

        foreach ($articles as $art) {
            Article::updateOrCreate(
                ['slug' => Str::slug($art['title'])],
                array_merge($art, ['author_id' => $admin->id])
            );
        }
    }
}
