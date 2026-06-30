<?php

namespace App\Services\External;

use App\Repositories\Contracts\PortRepositoryInterface;
use App\Services\Contracts\PortServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PortService implements PortServiceInterface
{
    protected PortRepositoryInterface $portRepository;

    public function __construct(PortRepositoryInterface $portRepository)
    {
        $this->portRepository = $portRepository;
    }

    /**
     * Paginate and filter ports list.
     */
    public function paginatePorts(int $perPage, array $filters = []): LengthAwarePaginator
    {
        return $this->portRepository->paginateFiltered($perPage, $filters);
    }

    /**
     * Get all active ports for mapping coordinates.
     */
    public function getActivePortsForMap(): Collection
    {
        return $this->portRepository->allActive()->map(function ($port) {
            return collect([
                'id' => $port->id,
                'name' => $port->name,
                'port_code' => $port->port_code,
                'latitude' => (float) $port->latitude,
                'longitude' => (float) $port->longitude,
                'port_type' => $port->port_type,
                'port_size' => $port->port_size,
                'country_name' => $port->country ? $port->country->name : 'Unknown',
                'country_code' => $port->country ? $port->country->iso2 : '',
                'view_url' => route('countries.show', $port->country ? $port->country->iso2 : '#'),
            ]);
        });
    }
}
