<?php

namespace App\Http\Controllers;

use App\Services\Contracts\PortServiceInterface;
use App\Services\RouteAnalysisService;
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

        // Fetch all active ports for searchable route analyzer dropdowns
        $allActivePorts = \App\Models\Port::with('country')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('user.ports_index', compact('ports', 'filters', 'mapPorts', 'countries', 'allActivePorts'));
    }

    /**
     * Analyze maritime shipping route between origin and destination ports.
     */
    public function analyzeRoute(Request $request, RouteAnalysisService $routeService)
    {
        $request->validate([
            'origin_port_id' => 'required|exists:ports,id',
            'destination_port_id' => 'required|exists:ports,id|different:origin_port_id',
            'priority' => 'nullable|in:safest,fastest,cheapest',
            'container_type' => 'nullable|in:general,container,liquid,bulk',
        ], [
            'destination_port_id.different' => 'Pelabuhan tujuan harus berbeda dengan pelabuhan asal.',
        ]);

        try {
            $result = $routeService->analyze(
                (int) $request->origin_port_id,
                (int) $request->destination_port_id,
                $request->input('priority', 'safest'),
                $request->input('container_type', 'container')
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan analisis rute: ' . $e->getMessage(),
            ], 500);
        }
    }
}
