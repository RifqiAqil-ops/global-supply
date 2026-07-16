@extends('layouts.app')

@section('title', 'Sourcing Risk Reports')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Sourcing Risk Reports</h1>
        <p class="text-muted small mb-0">Generate and download comprehensive analytics reports for global supply chain sourcing risks.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- PDF Export Option Card -->
    <div class="col-md-6">
        <div class="card card-premium border-0 h-100 p-3" style="background: linear-gradient(135deg, rgba(220, 53, 69, 0.05) 0%, rgba(220, 53, 69, 0.01) 100%);">
            <div class="card-body d-flex flex-column align-items-start">
                <div class="text-danger fs-3 mb-2"><i class="bi bi-file-earmark-pdf-fill"></i></div>
                <h5 class="text-white fw-bold mb-1">Executive PDF Briefing Report</h5>
                <p class="text-muted small mb-4">Download a beautifully typeset landscape A4 executive summary, complete with color-coded risk levels, category weights, and detailed metrics suited for leadership review.</p>
                <a href="{{ route('reports.export.pdf') }}" class="btn btn-danger btn-sm mt-auto fw-bold px-4 py-2">
                    <i class="bi bi-download me-1"></i> Export Executive PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Excel Export Option Card -->
    <div class="col-md-6">
        <div class="card card-premium border-0 h-100 p-3" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.05) 0%, rgba(25, 135, 84, 0.01) 100%);">
            <div class="card-body d-flex flex-column align-items-start">
                <div class="text-success fs-3 mb-2"><i class="bi bi-file-earmark-excel-fill"></i></div>
                <h5 class="text-white fw-bold mb-1">Native Excel Spreadsheet</h5>
                <p class="text-muted small mb-4">Download the complete risk scoring database in native Microsoft Excel (.xlsx) format. Contains ISO codes, scores, individual category indexes, and timestamp details.</p>
                <a href="{{ route('reports.export.excel') }}" class="btn btn-success btn-sm mt-auto fw-bold px-4 py-2">
                    <i class="bi bi-download me-1"></i> Export Excel (.xlsx)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Preview Card -->
<x-card title="Risk Scoring Report Preview" icon="bi-table">
    <div class="table-responsive">
        <x-table :headers="['Country Code', 'Country Name', 'Composite Score', 'Risk Level', 'Econ', 'Weather', 'Currency', 'Geo', 'Logistics', 'Last Calculated']">
            @forelse($scores as $item)
            @php
                $level = $item->risk_level;
                $badgeType = 'success';
                if ($level === 'high' || $level === 'critical') $badgeType = 'danger';
                elseif ($level === 'medium') $badgeType = 'warning';

                $details = $item->details->keyBy('riskCategory.slug');
            @endphp
            <tr>
                <td><strong>{{ $item->country->iso3 }}</strong></td>
                <td>
                    <a href="{{ route('countries.show', $item->country->iso2) }}" class="d-flex align-items-center gap-2 small text-white text-decoration-none hover-primary">
                        <img src="{{ $item->country->flag_url }}" alt="{{ $item->country->name }} Flag" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                        {{ $item->country->name }}
                    </a>
                </td>
                <td><strong class="text-white">{{ number_format($item->composite_score, 2) }}</strong></td>
                <td><x-badge type="{{ $badgeType }}">{{ ucfirst($level) }}</x-badge></td>
                <td><span class="small text-muted">{{ $details->has('economic-risk') ? number_format($details->get('economic-risk')->category_score, 1) : 'N/A' }}</span></td>
                <td><span class="small text-muted">{{ $details->has('weather-risk') ? number_format($details->get('weather-risk')->category_score, 1) : 'N/A' }}</span></td>
                <td><span class="small text-muted">{{ $details->has('currency-stability-risk') ? number_format($details->get('currency-stability-risk')->category_score, 1) : 'N/A' }}</span></td>
                <td><span class="small text-muted">{{ $details->has('geopolitical-risk') ? number_format($details->get('geopolitical-risk')->category_score, 1) : 'N/A' }}</span></td>
                <td><span class="small text-muted">{{ $details->has('logistics-risk') ? number_format($details->get('logistics-risk')->category_score, 1) : 'N/A' }}</span></td>
                <td class="small text-muted">{{ $item->calculated_at->diffForHumans() }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center text-muted py-4">No risk score entries available to preview.</td>
            </tr>
            @endforelse
        </x-table>
    </div>
</x-card>
@endsection
