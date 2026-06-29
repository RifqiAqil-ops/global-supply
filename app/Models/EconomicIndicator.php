<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EconomicIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'indicator_code',
        'indicator_name',
        'year',
        'value',
        'unit',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'value' => 'decimal:4',
        ];
    }

    /**
     * Get the country this indicator belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
