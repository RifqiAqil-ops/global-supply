<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CountryRiskScore;
use Illuminate\Http\Request;

class RiskHistoryController extends Controller
{
    /**
     * Display a listing of risk score calculation history.
     */
    public function index(Request $request)
    {
        $query = CountryRiskScore::with(['country', 'details.riskCategory']);

        // Search by country name/code if requested
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('country', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('iso2', 'like', "%{$search}%")
                  ->orWhere('iso3', 'like', "%{$search}%");
            });
        }

        // Filter by risk level
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->input('risk_level'));
        }

        $history = $query->orderByDesc('calculated_at')->paginate(15);

        return view('user.risk_history', compact('history'));
    }
}
