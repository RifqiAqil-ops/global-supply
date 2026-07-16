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

    public function getActivePortsForMap(array $filters = []): Collection
    {
        // Load with country and latest risk score relations eager-loaded to avoid N+1 queries
        $query = \App\Models\Port::where('ports.is_active', true)
            ->with(['country.latestRiskScore'])
            ->join('countries', 'countries.id', '=', 'ports.country_id')
            ->select('ports.*');

        // 1. Search filter
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('ports.name', 'like', "%{$search}%")
                  ->orWhere('ports.port_code', 'like', "%{$search}%")
                  ->orWhere('ports.un_locode', 'like', "%{$search}%")
                  ->orWhere('countries.name', 'like', "%{$search}%")
                  ->orWhere('countries.iso2', 'like', "%{$search}%")
                  ->orWhere('countries.iso3', 'like', "%{$search}%")
                  ->orWhere('ports.region', 'like', "%{$search}%")
                  ->orWhere('countries.region', 'like', "%{$search}%");
            });
        }

        // 2. Region filter
        if (!empty($filters['region']) && $filters['region'] !== 'all') {
            $region = $filters['region'];
            $query->where(function ($q) use ($region) {
                $q->where('countries.region', $region)
                  ->orWhere('ports.region', $region);
            });
        }

        // 3. Country filter
        if (!empty($filters['country_id']) && $filters['country_id'] !== 'all') {
            $query->where('ports.country_id', $filters['country_id']);
        }

        // 4. Harbor Size filter
        if (!empty($filters['harbor_size']) && $filters['harbor_size'] !== 'all') {
            $query->where('ports.port_size', $filters['harbor_size']);
        }

        // 5. Harbor Type filter
        if (!empty($filters['harbor_type']) && $filters['harbor_type'] !== 'all') {
            $query->where('ports.harbor_type', $filters['harbor_type']);
        }

        // 6. Operational Status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            $isActive = $filters['status'] === 'active' || $filters['status'] == 1;
            $query->where('ports.is_active', $isActive);
        }

        $ports = $query->get();

        return $ports->map(function ($port) {
            $riskScore = $port->country && $port->country->latestRiskScore 
                ? $port->country->latestRiskScore->composite_score 
                : null;

            return collect([
                'id' => $port->id,
                'name' => $port->name,
                'port_code' => $port->port_code,
                'un_locode' => $port->un_locode,
                'latitude' => (float) $port->latitude,
                'longitude' => (float) $port->longitude,
                'port_type' => $port->port_type,
                'port_size' => $port->port_size,
                'harbor_size' => $port->harbor_size,
                'harbor_type' => $port->harbor_type,
                'max_vessel_size' => $port->max_vessel_size,
                'risk_score' => $riskScore,
                'country_name' => $port->country ? $port->country->name : 'Unknown',
                'country_code' => $port->country ? $port->country->iso2 : '',
                'view_url' => route('countries.show', $port->country ? $port->country->iso2 : '#'),
            ]);
        });
    }
}
