<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CountryRiskScore;
use App\Models\Port;
use App\Models\NewsArticle;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * Get list of countries.
     */
    public function countries(): JsonResponse
    {
        $countries = Country::orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $countries
        ], 200);
    }

    /**
     * Get list of risk scores.
     */
    public function risk(): JsonResponse
    {
        $risks = CountryRiskScore::with('country')
            ->orderByDesc('composite_score')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $risks
        ], 200);
    }

    /**
     * Get list of ports.
     */
    public function ports(): JsonResponse
    {
        $ports = Port::with('country')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $ports
        ], 200);
    }

    /**
     * Get list of news articles.
     */
    public function news(): JsonResponse
    {
        $news = NewsArticle::with('country')
            ->orderByDesc('published_at')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $news
        ], 200);
    }

    /**
     * Get list of exchange rates.
     */
    public function currency(): JsonResponse
    {
        $rates = ExchangeRate::with('country')->orderBy('currency_code')->get();
        return response()->json([
            'success' => true,
            'data' => $rates
        ], 200);
    }
}
