<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'iso2',
        'iso3',
        'name',
        'official_name',
        'capital',
        'region',
        'sub_region',
        'latitude',
        'longitude',
        'population',
        'area',
        'flag_url',
        'flag_emoji',
        'currency_code',
        'currency_name',
        'currency_symbol',
        'calling_code',
        'tld',
        'timezones',
        'languages',
        'borders',
        'is_independent',
        'is_un_member',
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
            'population' => 'integer',
            'area' => 'decimal:2',
            'timezones' => 'array',
            'languages' => 'array',
            'borders' => 'array',
            'is_independent' => 'boolean',
            'is_un_member' => 'boolean',
        ];
    }

    /**
     * Get ports belonging to this country.
     */
    public function ports(): HasMany
    {
        return $this->hasMany(Port::class);
    }

    /**
     * Get economic indicators for this country.
     */
    public function economicIndicators(): HasMany
    {
        return $this->hasMany(EconomicIndicator::class);
    }

    /**
     * Get exchange rates for this country.
     */
    public function exchangeRates(): HasMany
    {
        return $this->hasMany(ExchangeRate::class);
    }

    /**
     * Get weather data for this country.
     */
    public function weatherData(): HasMany
    {
        return $this->hasMany(WeatherData::class);
    }

    /**
     * Get news articles about this country.
     */
    public function newsArticles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }

    /**
     * Get risk scores for this country.
     */
    public function riskScores(): HasMany
    {
        return $this->hasMany(CountryRiskScore::class);
    }

    /**
     * Get the latest risk score for this country.
     */
    public function latestRiskScore()
    {
        return $this->hasOne(CountryRiskScore::class)->latestOfMany('score_date');
    }

    /**
     * Get the latest weather data for this country.
     */
    public function latestWeather()
    {
        return $this->hasOne(WeatherData::class)->latestOfMany('fetched_at');
    }

    /**
     * Get watchlist items referencing this country.
     */
    public function watchlistItems(): HasMany
    {
        return $this->hasMany(WatchlistItem::class);
    }
}
