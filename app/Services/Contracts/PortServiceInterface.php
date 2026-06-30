<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PortServiceInterface
{
    /**
     * Paginate and filter ports list.
     */
    public function paginatePorts(int $perPage, array $filters = []): LengthAwarePaginator;

    /**
     * Get all active ports for mapping coordinates.
     */
    public function getActivePortsForMap(): Collection;
}
