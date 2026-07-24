<?php

namespace App\Http\Controllers\User;

use App\Events\WatchlistUpdated;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Watchlist;
use App\Models\WatchlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WatchlistController extends Controller
{
    /**
     * Display a listing of the watchlist items.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $watchlist = Watchlist::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Primary Watchlist']
        );

        $query = WatchlistItem::with(['country.latestRiskScore'])
            ->where('watchlist_id', $watchlist->id);

        // Search logic
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('country', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('iso2', 'like', "%{$search}%")
                  ->orWhere('iso3', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(10);
        $allCountries = Country::orderBy('name')->get();

        return view('user.watchlists_index', compact('items', 'watchlist', 'allCountries'));
    }

    /**
     * Store a newly created watchlist item in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
            'alert_threshold' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $watchlist = Watchlist::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'Primary Watchlist']
        );

        // Prevent duplicate entries
        $exists = WatchlistItem::where('watchlist_id', $watchlist->id)
            ->where('country_id', $request->input('country_id'))
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'This country is already in your watchlist.');
        }

        $item = WatchlistItem::create([
            'watchlist_id' => $watchlist->id,
            'country_id' => $request->input('country_id'),
            'alert_threshold' => $request->input('alert_threshold'),
            'notes' => $request->input('notes'),
        ]);

        try {
            WatchlistUpdated::dispatch($user->id, 'created', $item->toArray());
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', 'Country added to watchlist successfully.');
    }

    /**
     * Update the specified watchlist item.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'alert_threshold' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $item = WatchlistItem::findOrFail($id);
        
        // Security check: ensure user owns the parent watchlist
        if ($item->watchlist->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $item->update([
            'alert_threshold' => $request->input('alert_threshold'),
            'notes' => $request->input('notes'),
        ]);

        try {
            WatchlistUpdated::dispatch(Auth::id(), 'updated', $item->toArray());
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', 'Watchlist item updated successfully.');
    }

    /**
     * Remove the specified watchlist item from database.
     */
    public function destroy($id)
    {
        $item = WatchlistItem::findOrFail($id);

        // Security check
        if ($item->watchlist->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $itemData = $item->toArray();
        $item->delete();

        try {
            WatchlistUpdated::dispatch(Auth::id(), 'deleted', $itemData);
        } catch (\Throwable $e) {}

        return redirect()->back()->with('success', 'Country removed from watchlist successfully.');
    }
}
