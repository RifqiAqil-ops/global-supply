<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskWeight extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_category_id',
        'weight',
        'min_threshold',
        'max_threshold',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'min_threshold' => 'decimal:2',
            'max_threshold' => 'decimal:2',
        ];
    }

    /**
     * Get the risk category this weight belongs to.
     */
    public function riskCategory(): BelongsTo
    {
        return $this->belongsTo(RiskCategory::class);
    }
}
