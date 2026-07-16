<?php

namespace Database\Seeders;

use App\Models\PositiveWord;
use App\Models\NegativeWord;
use Illuminate\Database\Seeder;

class LexiconSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positiveWords = ['growth', 'increase', 'improved', 'recovery', 'surge', 'boom', 'agreement', 'deal', 'cooperation', 'reform', 'investment', 'expand'];
        $negativeWords = ['war', 'conflict', 'crisis', 'recession', 'inflation', 'sanctions', 'tariff', 'disruption', 'decline', 'collapse', 'protest', 'instability', 'ban', 'shortage'];

        foreach ($positiveWords as $word) {
            PositiveWord::firstOrCreate(['word' => $word]);
        }

        foreach ($negativeWords as $word) {
            NegativeWord::firstOrCreate(['word' => $word]);
        }
    }
}
