<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Watchlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get the user who owns this watchlist.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get items in this watchlist.
     */
    public function items(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }

    /**
     * Get countries in this watchlist through items.
     */
    public function countries()
    {
        return $this->hasManyThrough(
            Country::class,
            WatchlistItem::class,
            'watchlist_id',
            'id',
            'id',
            'country_id'
        );
    }
}
