<?php

namespace App\Exports;

use App\Models\CountryRiskScore;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * Fetch the raw dataset of country risk scores with nested relations.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return CountryRiskScore::with([
            'country.economicIndicators',
            'country.latestWeather',
            'details.riskCategory'
        ])->orderByDesc('composite_score')->get();
    }

    /**
     * Define the localized columns headers in Bahasa Indonesia.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Negara',
            'Skor Risiko',
            'Kategori Risiko',
            'GDP',
            'Inflasi',
            'Populasi',
            'Mata Uang',
            'Cuaca',
            'Terakhir Diperbarui'
        ];
    }

    /**
     * Map row fields correctly while preserving numeric types and formats.
     *
     * @param mixed $row
     * @return array
     */
    public function map($row): array
    {
        $country = $row->country;

        // GDP (Economic Indicator)
        $gdpVal = $country->economicIndicators
            ->where('indicator_code', 'NY.GDP.MKTP.CD')
            ->sortByDesc('year')
            ->first()?->value;
        $gdpFormatted = $gdpVal ? (float)$gdpVal : null;

        // Inflation (Economic Indicator)
        $inflationVal = $country->economicIndicators
            ->where('indicator_code', 'FP.CPI.TOTL.ZG')
            ->sortByDesc('year')
            ->first()?->value;
        $inflationFormatted = $inflationVal ? (float)$inflationVal : null;

        // Population
        $population = $country->population ? (int)$country->population : null;

        // Currency details
        $currency = $country->currency_code
            ? $country->currency_code . ' (' . $country->currency_name . ')'
            : 'N/A';

        // Weather details
        $weather = $country->latestWeather
            ? $country->latestWeather->temperature . '°C (' . $country->latestWeather->weather_description . ')'
            : 'N/A';

        // Translate risk levels to Bahasa Indonesia
        $level = $row->risk_level;
        $levelText = 'Risiko Rendah';
        if ($level === 'high' || $level === 'critical') {
            $levelText = 'Risiko Tinggi';
        } elseif ($level === 'medium') {
            $levelText = 'Risiko Sedang';
        }

        return [
            $country->name,
            (float)$row->composite_score,
            $levelText,
            $gdpFormatted,
            $inflationFormatted,
            $population,
            $currency,
            $weather,
            $row->calculated_at->format('Y-m-d H:i:s')
        ];
    }
}
