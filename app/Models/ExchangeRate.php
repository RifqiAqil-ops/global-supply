<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'country_id',
        'currency_code',
        'currency_name',
        'rate_to_usd',
        'rate_to_idr',
        'change_percent',
        'rate_date',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'rate_to_usd' => 'decimal:10',
            'rate_to_idr' => 'decimal:4',
            'change_percent' => 'decimal:4',
            'rate_date' => 'date',
        ];
    }

    /**
     * Get the country this exchange rate belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
