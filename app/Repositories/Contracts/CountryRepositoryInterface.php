<?php

namespace App\Repositories\Contracts;

use App\Interfaces\RepositoryInterface;
use App\Models\Country;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CountryRepositoryInterface extends RepositoryInterface
{
    /**
     * Find country by ISO2 or ISO3 code.
     *
     * @param string $code
     * @return Country|null
     */
    public function findByCode(string $code): ?Country;

    /**
     * Find country by common or official name.
     *
     * @param string $name
     * @return Country|null
     */
    public function findByName(string $name): ?Country;

    /**
     * Search countries by name, region, or code.
     *
     * @param string $query
     * @return Collection
     */
    public function search(string $query): Collection;

    /**
     * Paginate countries with optional search and filters.
     *
     * @param int $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function paginateFiltered(int $perPage, array $filters = []): LengthAwarePaginator;
}
