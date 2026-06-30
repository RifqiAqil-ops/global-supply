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
        $filters = $request->only(['search', 'region']);

        // Paginated list (10 per page)
        $ports = $this->portService->paginatePorts(10, $filters);
        
        // Load all active ports to plot as markers on Leaflet
        $mapPorts = $this->portService->getActivePortsForMap();

        return view('user.ports_index', compact('ports', 'filters', 'mapPorts'));
    }
}
