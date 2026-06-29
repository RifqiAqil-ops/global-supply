<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'news_article_id',
        'notes',
    ];

    /**
     * Get the user who created this bookmark.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the bookmarked article.
     */
    public function newsArticle(): BelongsTo
    {
        return $this->belongsTo(NewsArticle::class);
    }
}
