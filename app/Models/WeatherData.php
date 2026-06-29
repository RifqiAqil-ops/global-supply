<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherData extends Model
{
    use HasFactory;

    protected $table = 'weather_data';

    protected $fillable = [
        'country_id',
        'city_name',
        'latitude',
        'longitude',
        'temperature',
        'feels_like',
        'humidity',
        'wind_speed',
        'wind_direction',
        'precipitation',
        'pressure',
        'visibility',
        'uv_index',
        'weather_code',
        'weather_description',
        'is_extreme',
        'daily_forecast',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'temperature' => 'decimal:2',
            'feels_like' => 'decimal:2',
            'humidity' => 'decimal:2',
            'wind_speed' => 'decimal:2',
            'wind_direction' => 'integer',
            'precipitation' => 'decimal:2',
            'pressure' => 'decimal:2',
            'visibility' => 'decimal:2',
            'uv_index' => 'decimal:2',
            'weather_code' => 'integer',
            'is_extreme' => 'boolean',
            'daily_forecast' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    /**
     * Get the country this weather data belongs to.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
