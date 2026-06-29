<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScoreDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_risk_score_id',
        'risk_category_id',
        'category_score',
        'weighted_score',
        'scoring_data',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'category_score' => 'decimal:2',
            'weighted_score' => 'decimal:2',
            'scoring_data' => 'array',
        ];
    }

    /**
     * Get the parent risk score.
     */
    public function riskScore(): BelongsTo
    {
        return $this->belongsTo(CountryRiskScore::class, 'country_risk_score_id');
    }

    /**
     * Get the risk category.
     */
    public function riskCategory(): BelongsTo
    {
        return $this->belongsTo(RiskCategory::class);
    }
}
