<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Port extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'country_id',
        'name',
        'port_code',
        'latitude',
        'longitude',
        'port_type',
        'port_size',
        'harbor_type',
        'shelter',
        'max_vessel_length',
        'max_depth',
        'facilities',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'max_vessel_length' => 'integer',
            'max_depth' => 'decimal:2',
            'facilities' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the country this port belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
