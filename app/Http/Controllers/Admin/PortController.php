<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Port;
use App\Models\Country;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index(Request $request)
    {
        $query = Port::with('country')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('country', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $ports = $query->paginate(15)->withQueryString();
        return view('admin.ports.index', compact('ports'));
    }

    public function create()
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.ports.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:ports'],
            'country_id' => ['required', 'exists:countries,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $port = Port::create($validated);

        try {
            \App\Events\PortChanged::dispatch('created', $port->toArray());
        } catch (\Throwable $e) {}

        return redirect()->route('admin.ports.index')->with('success', 'Port created successfully.');
    }

    public function edit(Port $port)
    {
        $countries = Country::orderBy('name')->get();
        return view('admin.ports.edit', compact('port', 'countries'));
    }

    public function update(Request $request, Port $port)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:ports,code,' . $port->id],
            'country_id' => ['required', 'exists:countries,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'status' => ['required', 'string', 'max:50'],
        ]);

        $port->update($validated);

        try {
            \App\Events\PortChanged::dispatch('updated', $port->toArray());
        } catch (\Throwable $e) {}

        return redirect()->route('admin.ports.index')->with('success', 'Port updated successfully.');
    }

    public function destroy(Port $port)
    {
        $portData = $port->toArray();
        $port->delete();

        try {
            \App\Events\PortChanged::dispatch('deleted', $portData);
        } catch (\Throwable $e) {}

        return redirect()->route('admin.ports.index')->with('success', 'Port deleted successfully.');
    }
}
