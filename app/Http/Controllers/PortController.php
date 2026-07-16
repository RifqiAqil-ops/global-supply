<?php

namespace App\Http\Controllers;

use App\Services\Contracts\PortServiceInterface;
use Illuminate\Http\Request;

class PortController extends Controller
{
    protected PortServiceInterface $portService;

    public function __construct(PortServiceInterface $portService)
    {
        $this->portService = $portService;
    }

    /**
     * Show Ports Dashboard.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'region', 'country_id', 'harbor_size', 'harbor_type', 'status']);

        // Paginated list (10 per page)
        $ports = $this->portService->paginatePorts(10, $filters);
        
        // Load all active ports to plot as markers on Leaflet
        $mapPorts = $this->portService->getActivePortsForMap($filters);

        if ($request->ajax()) {
            $html = view('user.partials.ports_table_rows', compact('ports'))->render();
            $paginationHtml = $ports->hasPages() ? $ports->appends(request()->query())->links('pagination::bootstrap-5')->render() : '';
            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $paginationHtml,
                'mapPorts' => $mapPorts,
                'total' => $ports->total(),
            ]);
        }

        // Fetch countries for filter dropdown
        $countries = \App\Models\Country::orderBy('name')->get();

        return view('user.ports_index', compact('ports', 'filters', 'mapPorts', 'countries'));
    }
}
