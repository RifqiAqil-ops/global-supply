<?php

namespace App\Repositories;

use App\Models\Country;
use App\Repositories\Contracts\CountryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }

    /**
     * Find country by ISO2 or ISO3 code.
     */
    public function findByCode(string $code): ?Country
    {
        $code = strtoupper(trim($code));
        return $this->model->where('iso2', $code)
            ->orWhere('iso3', $code)
            ->first();
    }

    /**
     * Find country by common or official name.
     */
    public function findByName(string $name): ?Country
    {
        $name = trim($name);
        return $this->model->where('name', 'like', $name)
            ->orWhere('official_name', 'like', $name)
            ->first();
    }

    /**
     * Search countries by name, region, or code.
     */
    public function search(string $query): Collection
    {
        $query = trim($query);
        if (empty($query)) {
            return new Collection();
        }

        return $this->model->where('name', 'like', "%{$query}%")
            ->orWhere('official_name', 'like', "%{$query}%")
            ->orWhere('iso2', 'like', "%{$query}%")
            ->orWhere('iso3', 'like', "%{$query}%")
            ->orWhere('region', 'like', "%{$query}%")
            ->get();
    }

    /**
     * Paginate countries with optional filters.
     */
    public function paginateFiltered(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        // 1. Search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('official_name', 'like', "%{$search}%")
                  ->orWhere('iso2', 'like', "%{$search}%")
                  ->orWhere('iso3', 'like', "%{$search}%");
            });
        }

        // 2. Region filter
        if (!empty($filters['region'])) {
            $query->where('region', $filters['region']);
        }

        // 3. Population range filter
        if (!empty($filters['population_range'])) {
            $range = $filters['population_range'];
            if ($range === 'under_1m') {
                $query->where('population', '<', 1000000);
            } elseif ($range === '1m_10m') {
                $query->whereBetween('population', [1000000, 10000000]);
            } elseif ($range === '10m_100m') {
                $query->whereBetween('population', [10000000, 100000000]);
            } elseif ($range === 'over_100m') {
                $query->where('population', '>', 100000000);
            }
        }

        // 4. GDP range filter (using subquery)
        if (!empty($filters['gdp_range'])) {
            $range = $filters['gdp_range'];
            $gdpSub = \App\Models\EconomicIndicator::select('value')
                ->whereColumn('country_id', 'countries.id')
                ->where('indicator_code', 'NY.GDP.MKTP.CD')
                ->orderByDesc('year')
                ->limit(1);

            if ($range === 'under_10b') {
                $query->where(function ($q) use ($gdpSub) {
                    $q->where($gdpSub, '<', 10000000000);
                });
            } elseif ($range === '10b_100b') {
                $query->where(function ($q) use ($gdpSub) {
                    $q->where($gdpSub, '>=', 10000000000)
                      ->where($gdpSub, '<=', 100000000000);
                });
            } elseif ($range === '100b_1t') {
                $query->where(function ($q) use ($gdpSub) {
                    $q->where($gdpSub, '>=', 100000000000)
                      ->where($gdpSub, '<=', 1000000000000);
                });
            } elseif ($range === 'over_1t') {
                $query->where(function ($q) use ($gdpSub) {
                    $q->where($gdpSub, '>', 1000000000000);
                });
            }
        }

        // 5. Inflation range filter (using subquery)
        if (!empty($filters['inflation_range'])) {
            $range = $filters['inflation_range'];
            $infSub = \App\Models\EconomicIndicator::select('value')
                ->whereColumn('country_id', 'countries.id')
                ->where('indicator_code', 'FP.CPI.TOTL.ZG')
                ->orderByDesc('year')
                ->limit(1);

            if ($range === 'deflation') {
                $query->where(function ($q) use ($infSub) {
                    $q->where($infSub, '<', 0);
                });
            } elseif ($range === 'low') {
                $query->where(function ($q) use ($infSub) {
                    $q->where($infSub, '>=', 0)
                      ->where($infSub, '<=', 3);
                });
            } elseif ($range === 'moderate') {
                $query->where(function ($q) use ($infSub) {
                    $q->where($infSub, '>=', 3)
                      ->where($infSub, '<=', 10);
                });
            } elseif ($range === 'high') {
                $query->where(function ($q) use ($infSub) {
                    $q->where($infSub, '>', 10);
                });
            }
        }

        // 6. Sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $direction = $filters['sort_dir'] ?? 'asc';
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, ['gdp', 'gdp_capita', 'inflation'])) {
            $code = match($sortBy) {
                'gdp' => 'NY.GDP.MKTP.CD',
                'gdp_capita' => 'NY.GDP.PCAP.CD',
                'inflation' => 'FP.CPI.TOTL.ZG',
            };
            
            $query->orderBy(
                \App\Models\EconomicIndicator::select('value')
                    ->whereColumn('country_id', 'countries.id')
                    ->where('indicator_code', $code)
                    ->orderByDesc('year')
                    ->limit(1),
                $direction
            );
        } else {
            $allowedSorts = ['name', 'population', 'area'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'name';
            }
            $query->orderBy($sortBy, $direction);
        }

        return $query->paginate($perPage);
    }
}
