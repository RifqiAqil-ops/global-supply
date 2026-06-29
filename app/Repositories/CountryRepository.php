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

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('official_name', 'like', "%{$search}%")
                  ->orWhere('iso2', $search)
                  ->orWhere('iso3', $search);
            });
        }

        if (!empty($filters['region'])) {
            $query->where('region', $filters['region']);
        }

        if (!empty($filters['sub_region'])) {
            $query->where('sub_region', $filters['sub_region']);
        }

        if (isset($filters['is_independent'])) {
            $query->where('is_independent', (bool) $filters['is_independent']);
        }

        // Sort results
        $sortBy = $filters['sort_by'] ?? 'name';
        $direction = $filters['sort_dir'] ?? 'asc';
        
        $allowedSorts = ['name', 'population', 'area', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage);
    }
}
