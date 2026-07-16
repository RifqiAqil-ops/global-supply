<?php

namespace App\Repositories;

use App\Models\Port;
use App\Repositories\Contracts\PortRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PortRepository extends BaseRepository implements PortRepositoryInterface
{
    public function __construct(Port $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a port by its unique code.
     */
    public function findByCode(string $code): ?Port
    {
        return $this->model->where('port_code', strtoupper(trim($code)))->first();
    }

    /**
     * Get all active ports.
     */
    public function allActive(): Collection
    {
        return $this->model->where('is_active', true)
            ->with(['country'])
            ->get();
    }

    /**
     * Paginate and filter ports directory.
     */
    public function paginateFiltered(int $perPage, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query()->with(['country']);

        // Join countries table to allow searching by country name/code and region filtering
        $query->select('ports.*')
            ->join('countries', 'countries.id', '=', 'ports.country_id');

        // 1. Search filter (port name, port code, un_locode, country name, country code, region)
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

        // 2. Region filter (associated via countries or ports)
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

        // Sort by port name by default
        $query->orderBy('ports.name', 'asc');

        return $query->paginate($perPage);
    }
}
