<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NewsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'title',
        'description',
        'content',
        'source_name',
        'source_url',
        'image_url',
        'category',
        'sentiment',
        'search_query',
        'published_at',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'fetched_at' => 'datetime',
        ];
    }

    /**
     * Get the country this article is about.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get bookmarks for this article.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(NewsBookmark::class);
    }
}
