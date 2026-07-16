@extends('layouts.app')

@section('title', 'Edit Port')

@section('content')
<div class="container-fluid py-4" style="max-width: 680px;">
    <!-- Back Link -->
    <div class="mb-4">
        <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary-saas">
            <i class="bi bi-arrow-left"></i> Back to Ports
        </a>
    </div>

    <!-- Form card -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 24px; border: 1px solid #E5E7EB !important; background-color: #FFFFFF;">
        <h2 class="typo-h2 mb-2">Edit Port: {{ $port->name }}</h2>
        <p class="text-muted small mb-4">Modify port operational logs, location coordinates, or sovereign flags.</p>

        <form action="{{ route('admin.ports.update', $port->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label for="name" class="form-label text-dark fw-semibold small">Port Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $port->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label for="code" class="form-label text-dark fw-semibold small">Port Code</label>
                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $port->code) }}" required>
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="country_id" class="form-label text-dark fw-semibold small">Sovereign Country</label>
                <select name="country_id" id="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                    @foreach($countries as $country)
                        <option value="{{ $country->id }}" {{ old('country_id', $port->country_id) == $country->id ? 'selected' : '' }}>
                            {{ $country->name }} ({{ $country->iso_code }})
                        </option>
                    @endforeach
                </select>
                @error('country_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="latitude" class="form-label text-dark fw-semibold small">Latitude</label>
                    <input type="number" step="any" name="latitude" id="latitude" class="form-control @error('latitude') is-invalid @enderror" value="{{ old('latitude', $port->latitude) }}" required>
                    @error('latitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="longitude" class="form-label text-dark fw-semibold small">Longitude</label>
                    <input type="number" step="any" name="longitude" id="longitude" class="form-control @error('longitude') is-invalid @enderror" value="{{ old('longitude', $port->longitude) }}" required>
                    @error('longitude')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label text-dark fw-semibold small">Logistics Status</label>
                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                    <option value="Operational" {{ old('status', $port->status) === 'Operational' ? 'selected' : '' }}>Operational</option>
                    <option value="Congested" {{ old('status', $port->status) === 'Congested' ? 'selected' : '' }}>Congested</option>
                    <option value="Closed" {{ old('status', $port->status) === 'Closed' ? 'selected' : '' }}>Closed</option>
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="pt-3 border-top mt-4 d-flex justify-content-end gap-2">
                <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary-saas">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Port</button>
            </div>
        </form>
    </div>
</div>
@endsection
