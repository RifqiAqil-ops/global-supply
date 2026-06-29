<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'watchlist_id',
        'country_id',
        'alert_threshold',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'alert_threshold' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Get the watchlist this item belongs to.
     */
    public function watchlist(): BelongsTo
    {
        return $this->belongsTo(Watchlist::class);
    }

    /**
     * Get the country this item references.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
