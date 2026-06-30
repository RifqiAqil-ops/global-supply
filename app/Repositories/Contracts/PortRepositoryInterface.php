<?php

namespace App\Repositories\Contracts;

use App\Models\Port;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PortRepositoryInterface
{
    /**
     * Find a port by its unique code.
     */
    public function findByCode(string $code): ?Port;

    /**
     * Get all active ports.
     */
    public function allActive(): Collection;

    /**
     * Paginate and filter ports directory.
     */
    public function paginateFiltered(int $perPage, array $filters = []): LengthAwarePaginator;
}
