<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RiskCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the weight configuration for this category.
     */
    public function weight(): HasOne
    {
        return $this->hasOne(RiskWeight::class);
    }

    /**
     * Get risk score details for this category.
     */
    public function scoreDetails(): HasMany
    {
        return $this->hasMany(RiskScoreDetail::class);
    }
}
