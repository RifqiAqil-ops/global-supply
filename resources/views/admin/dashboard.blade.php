@extends('layouts.app')

@section('title', 'Admin Console')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-white mb-1">Administration Control Console</h1>
        <p class="text-muted small mb-0">Manage users, adjust scoring algorithms, monitor external API integration health, and audit trails.</p>
    </div>
    <div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 py-2 px-3 fw-semibold">
            <i class="bi bi-shield-lock me-1"></i> Admin Privileges Enabled
        </span>
    </div>
</div>

<!-- Operations Stats -->
<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Total Users Registered" 
            value="{{ $totalUsers }}" 
            change="+1 this week" 
            changeType="up" 
            icon="bi-people" 
            iconColor="primary" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="API Calls (Last 24h)" 
            value="{{ number_format($apiCallsCount) }}" 
            change="{{ $successRate }}% Success Rate" 
            changeType="neutral" 
            icon="bi-cpu" 
            iconColor="success" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Failed Jobs / Errors" 
            value="0" 
            change="All Clear" 
            changeType="neutral" 
            icon="bi-bug" 
            iconColor="success" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="Avg Response Latency" 
            value="{{ $avgLatency }}ms" 
            change="Optimized" 
            changeType="down" 
            icon="bi-speedometer" 
            iconColor="info" 
        />
    </div>
</div>

<!-- API Integration Health Monitor Section -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <x-card title="External API Integration Status" icon="bi-cloud-check">
            <x-slot name="headerActions">
                <button id="btnDiagnoseAll" class="btn btn-sm btn-outline-primary py-1 px-3 fs-7">
                    <span id="diagnoseSpinner" class="spinner-border spinner-border-sm me-1 d-none" role="status" aria-hidden="true"></span>
                    <i id="diagnoseIcon" class="bi bi-activity me-1"></i> Diagnose All
                </button>
            </x-slot>
            
            <x-table :headers="['API Service Provider', 'Endpoint Base', 'Avg Latency', 'Status', 'Last Checked']">
                <tr id="row-rest-countries">
                    <td><strong>REST Countries</strong></td>
                    <td class="small text-muted">/countries.json</td>
                    <td class="latency-cell">Cached</td>
                    <td class="status-cell"><x-badge type="success">Active (200)</x-badge></td>
                    <td class="time-cell small text-muted">Ready</td>
                </tr>
                <tr id="row-world-bank">
                    <td><strong>World Bank API</strong></td>
                    <td class="small text-muted">/country/{code}</td>
                    <td class="latency-cell">Cached</td>
                    <td class="status-cell"><x-badge type="success">Active (200)</x-badge></td>
                    <td class="time-cell small text-muted">Ready</td>
                </tr>
                <tr id="row-open-meteo">
                    <td><strong>Open-Meteo</strong></td>
                    <td class="small text-muted">/forecast</td>
                    <td class="latency-cell">Cached</td>
                    <td class="status-cell"><x-badge type="success">Active (200)</x-badge></td>
                    <td class="time-cell small text-muted">Ready</td>
                </tr>
                <tr id="row-exchange-rate">
                    <td><strong>ExchangeRate API</strong></td>
                    <td class="small text-muted">/latest/{base}</td>
                    <td class="latency-cell">Cached</td>
                    <td class="status-cell"><x-badge type="success">Active (200)</x-badge></td>
                    <td class="time-cell small text-muted">Ready</td>
                </tr>
                <tr id="row-gnews">
                    <td><strong>GNews API</strong></td>
                    <td class="small text-muted">/search</td>
                    <td class="latency-cell">Cached</td>
                    <td class="status-cell"><x-badge type="warning">Key Missing (401)</x-badge></td>
                    <td class="time-cell small text-muted">Ready</td>
                </tr>
            </x-table>
        </x-card>
    </div>

    <!-- Algorithm Weights View Section -->
    <div class="col-lg-4">
        <x-card title="Composite Score Weights" icon="bi-sliders">
            <div class="d-flex flex-column gap-3">
                @foreach($weights as $w)
                @php
                    $percent = (float)$w->weight * 100;
                    $colorClass = 'bg-primary';
                    if ($w->riskCategory->slug === 'currency-stability-risk') $colorClass = 'bg-success';
                    elseif ($w->riskCategory->slug === 'weather-risk') $colorClass = 'bg-warning';
                    elseif ($w->riskCategory->slug === 'logistics-risk') $colorClass = 'bg-info';
                    elseif ($w->riskCategory->slug === 'economic-risk') $colorClass = 'bg-danger';
                @endphp
                <div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>{{ $w->riskCategory->name }}</span>
                        <span class="text-white fw-semibold">{{ $percent }}% ({{ $w->weight }})</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--color-border);">
                        <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                @endforeach
                
                <div class="pt-2 border-top border-secondary border-opacity-10 d-grid">
                    <button class="btn btn-sm btn-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalEditWeights">
                        <i class="bi bi-pencil me-1"></i> Edit Scoring Weights
                    </button>
                </div>
            </div>
        </x-card>
    </div>
</div>

<!-- Audit Trails & System Configuration Quick Links -->
<div class="row g-4">
    <!-- System Configs -->
    <div class="col-lg-6">
        <x-card title="Active Cache & System Configs" icon="bi-sliders2">
            <x-table :headers="['Configuration Key', 'Scope', 'Value']">
                <tr>
                    <td><code>cache_duration_weather</code></td>
                    <td><x-badge type="info">Cache</x-badge></td>
                    <td>30 minutes</td>
                </tr>
                <tr>
                    <td><code>cache_duration_news</code></td>
                    <td><x-badge type="info">Cache</x-badge></td>
                    <td>60 minutes</td>
                </tr>
                <tr>
                    <td><code>risk_score_high_max</code></td>
                    <td><x-badge type="danger">Risk</x-badge></td>
                    <td>75.00</td>
                </tr>
                <tr>
                    <td><code>max_comparison_countries</code></td>
                    <td><x-badge type="primary">Display</x-badge></td>
                    <td>4 countries</td>
                </tr>
            </x-table>
        </x-card>
    </div>

    <!-- Admin Activity Audit Logs -->
    <div class="col-lg-6">
        <x-card title="Recent Administrator Actions Log" icon="bi-journal-text">
            <div class="d-flex flex-column gap-3">
                @foreach($recentActions as $action)
                <div class="d-flex gap-2 align-items-start">
                    <div class="text-{{ $action['type'] }} mt-0.5"><i class="bi {{ $action['icon'] }}"></i></div>
                    <div>
                        <span class="text-white small fw-semibold">{{ $action['title'] }}</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">{{ $action['description'] }}</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">{{ $action['time'] }} &bull; IP: {{ $action['ip'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>

<!-- System Risk Alerts Log Row -->
<div class="row g-4 mt-2">
    <div class="col-12">
        <x-card title="System Risk Alerts Log" icon="bi-bell-fill">
            <div class="d-flex flex-column gap-2">
                @forelse($recentAlerts as $alert)
                <div class="d-flex gap-3 align-items-start p-2.5 rounded border border-danger border-opacity-10" style="background-color: rgba(220, 53, 69, 0.02);">
                    <div class="text-danger mt-0.5"><i class="bi bi-exclamation-triangle-fill fs-5"></i></div>
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-white small fw-bold">{{ $alert->description }}</span>
                            <span class="text-muted small" style="font-size: 0.72rem;">{{ $alert->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                            Change details: score shifted from 
                            <span class="text-warning fw-semibold">{{ number_format($alert->old_values['composite_score'] ?? 0, 2) }}</span> 
                            to 
                            <span class="text-danger fw-semibold">{{ number_format($alert->new_values['composite_score'] ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-shield-check display-6 d-block mb-2 text-success"></i>
                    <p class="small mb-0">No risk alert logs triggered in the system.</p>
                </div>
                @endforelse
            </div>
        </x-card>
    </div>
</div>

<!-- Modal for editing risk weights -->
<div class="modal fade" id="modalEditWeights" tabindex="-1" aria-labelledby="modalEditWeightsLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary border border-opacity-50">
            <div class="modal-header border-secondary border-opacity-30">
                <h5 class="modal-title text-white" id="modalEditWeightsLabel"><i class="bi bi-sliders me-2 text-primary"></i>Adjust Risk Weights</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.weights.update') }}" method="POST" id="formEditWeights">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small">Update the proportional weights of each risk category. The total sum of all weights must equal exactly 100%.</p>
                    
                    <div class="d-flex flex-column gap-3">
                        @foreach($weights as $w)
                        <div>
                            <label class="form-label text-white small mb-1">{{ $w->riskCategory->name }} (%)</label>
                            <input type="number" step="1" min="0" max="100" class="form-control bg-transparent text-white border-secondary weight-input" data-category-id="{{ $w->risk_category_id }}" name="weights[{{ $w->risk_category_id }}]" value="{{ (float)$w->weight * 100 }}">
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-4 p-3 rounded border border-secondary border-opacity-30 bg-black bg-opacity-30">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Current Total Weight:</span>
                            <span class="fw-bold fs-5 text-white" id="totalWeightIndicator">100%</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary border-opacity-30">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmitWeights">Save Weights</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- AJAX Diagnostics & Modal Sum Calculation Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. AJAX Diagnostics Check
    const btnDiagnose = document.getElementById('btnDiagnoseAll');
    const diagnoseSpinner = document.getElementById('diagnoseSpinner');
    const diagnoseIcon = document.getElementById('diagnoseIcon');

    btnDiagnose.addEventListener('click', function() {
        // Toggle spinner UI
        btnDiagnose.disabled = true;
        diagnoseSpinner.classList.remove('d-none');
        diagnoseIcon.classList.add('d-none');

        fetch("{{ route('admin.diagnose-api') }}")
            .then(response => response.json())
            .then(data => {
                data.forEach(service => {
                    let rowId = '';
                    if (service.name === 'REST Countries') rowId = 'row-rest-countries';
                    else if (service.name === 'World Bank API') rowId = 'row-world-bank';
                    else if (service.name === 'Open-Meteo') rowId = 'row-open-meteo';
                    else if (service.name === 'ExchangeRate API') rowId = 'row-exchange-rate';
                    else if (service.name === 'GNews API') rowId = 'row-gnews';

                    const row = document.getElementById(rowId);
                    if (row) {
                        row.querySelector('.latency-cell').innerText = service.latency;
                        row.querySelector('.status-cell').innerHTML = `<span class="badge bg-${service.status_type} bg-opacity-10 text-${service.status_type} border border-${service.status_type} border-opacity-20 py-1 px-2 fw-semibold fs-7">${service.status_text}</span>`;
                        row.querySelector('.time-cell').innerText = service.last_checked;
                    }
                });
            })
            .catch(error => {
                console.error('Error running API diagnostics:', error);
            })
            .finally(() => {
                btnDiagnose.disabled = false;
                diagnoseSpinner.classList.add('d-none');
                diagnoseIcon.classList.remove('d-none');
            });
    });

    // 2. Weights Sum Calculation validation
    const weightInputs = document.querySelectorAll('.weight-input');
    const totalIndicator = document.getElementById('totalWeightIndicator');
    const btnSubmitWeights = document.getElementById('btnSubmitWeights');

    function calculateTotalWeight() {
        let sum = 0;
        weightInputs.forEach(input => {
            sum += parseFloat(input.value || 0);
        });

        totalIndicator.innerText = sum + '%';

        if (sum === 100) {
            totalIndicator.className = "fw-bold fs-5 text-success";
            btnSubmitWeights.disabled = false;
        } else {
            totalIndicator.className = "fw-bold fs-5 text-danger";
            btnSubmitWeights.disabled = true;
        }
    }

    weightInputs.forEach(input => {
        input.addEventListener('input', calculateTotalWeight);
    });

    calculateTotalWeight(); // run on page load init
});
</script>
@endsection
