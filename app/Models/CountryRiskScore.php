<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CountryRiskScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'composite_score',
        'risk_level',
        'previous_score',
        'score_change',
        'data_completeness',
        'score_date',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'composite_score' => 'decimal:2',
            'previous_score' => 'decimal:2',
            'score_change' => 'decimal:2',
            'data_completeness' => 'decimal:2',
            'score_date' => 'date',
            'calculated_at' => 'datetime',
        ];
    }

    /**
     * Get the country this risk score belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the score breakdown details.
     */
    public function details(): HasMany
    {
        return $this->hasMany(RiskScoreDetail::class);
    }
}
