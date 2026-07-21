@extends('layouts.app')

@section('title', 'Manage Risk Scoring Weights')

@section('content')
@php
    $lastUpdated = $weights->max('updated_at');
@endphp
<div class="container-fluid py-4" style="max-width: 1280px; margin: 0 auto;">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Admin Console</a></li>
            <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">Risk Weights</li>
        </ol>
    </nav>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 text-dark mb-1">Risk Scoring Configuration</h1>
            <p class="text-muted small mb-0">Adjust the proportional significance of economic, weather, geopolitical, logistics, and currency stability factors in the country risk rating algorithm.</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Summary Panel (Right Column on Desktop/Tablet, Top on Mobile) -->
        <div class="col-md-4 order-md-2">
            <div class="card border shadow-sm p-4 mb-4" style="border-radius: 16px; background-color: #FFFFFF; border-color: #E5E7EB !important;">
                <h5 class="fw-bold text-dark mb-4" style="font-size: 1.1rem; border-bottom: 0px;">Weight Allocation Summary</h5>
                
                <!-- Large total and validation status indicator -->
                <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div>
                        <div class="text-muted small uppercase tracking-wider mb-1" style="font-weight: 700; font-size: 0.65rem; letter-spacing: 0.05em;">Total Weight</div>
                        <div class="display-6 fw-bold text-dark" id="totalAllocationIndicator" style="line-height: 1;">100%</div>
                    </div>
                    <div id="validationBanner" class="px-3 py-2 rounded-pill fw-semibold small d-flex align-items-center gap-1.5 shadow-sm" style="transition: all 0.3s ease;">
                        <i id="validationIcon" class="bi"></i>
                        <span id="validationTitle"></span>
                    </div>
                </div>

                <!-- Donut Chart Container -->
                <div class="position-relative d-flex justify-content-center align-items-center mb-4" style="height: 180px;">
                    <canvas id="weightsDoughnutChart" style="max-height: 180px; max-width: 180px;"></canvas>
                    <div class="position-absolute d-flex flex-column align-items-center justify-content-center" style="pointer-events: none; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <span class="text-muted small fw-semibold uppercase" style="font-size: 0.6rem; letter-spacing: 0.05em;">Allocated</span>
                        <span class="fs-4 fw-bold text-dark" id="donutTotalIndicator">100%</span>
                    </div>
                </div>

                <!-- Category legend listing -->
                <div class="d-flex flex-column gap-2 mb-4">
                    @foreach($weights as $w)
                    @php
                        $percent = (float)$w->weight * 100;
                        $colorClass = 'bg-primary';
                        if ($w->riskCategory->slug === 'currency-stability-risk') $colorClass = 'bg-success';
                        elseif ($w->riskCategory->slug === 'weather-risk') $colorClass = 'bg-warning';
                        elseif ($w->riskCategory->slug === 'logistics-risk') $colorClass = 'bg-info';
                        elseif ($w->riskCategory->slug === 'economic-risk') $colorClass = 'bg-danger';
                    @endphp
                    <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light" style="font-size: 0.85rem;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle d-inline-block {{ $colorClass }}" style="width: 10px; height: 10px;"></span>
                            <span class="text-dark fw-medium" style="font-size: 0.82rem;">{{ $w->riskCategory->name }}</span>
                        </div>
                        <span class="fw-bold text-dark" id="legendPercent-{{ $w->risk_category_id }}">{{ $percent }}%</span>
                    </div>
                    @endforeach
                </div>

                <!-- Current Formula section -->
                <div class="mb-4">
                    <span class="text-muted small uppercase tracking-wider d-block mb-2" style="font-weight: 700; font-size: 0.65rem; letter-spacing: 0.05em;">Current Formula</span>
                    <div class="p-3 rounded border border-light" style="border-radius: 12px; background-color: #F8FAFC; border-color: #F1F5F9 !important;">
                        <div class="fw-bold text-dark small mb-2">Risk Score =</div>
                        <div class="text-muted small" style="font-size: 0.78rem; line-height: 1.6;" id="formulaDetails">
                            <!-- Dynamically populated by JS -->
                        </div>
                    </div>
                </div>

                <!-- Metadata section -->
                <div class="pt-3 border-top" style="border-color: #F1F5F9 !important; font-size: 0.78rem;">
                    <div class="d-flex justify-content-between mb-1.5">
                        <span class="text-muted">Updated By:</span>
                        <span class="text-dark fw-semibold">Administrator</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Last Updated:</span>
                        <span class="text-dark fw-semibold">{{ $lastUpdated ? $lastUpdated->format('M d, Y H:i A') : 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form Column (Left Column on Desktop/Tablet, Bottom on Mobile) -->
        <div class="col-md-8 order-md-1">
            <div class="card border shadow-sm p-4 mb-4" style="border-radius: 16px; background-color: #FFFFFF; border-color: #E5E7EB !important;">
                <h5 class="fw-bold text-dark mb-3" style="font-size: 1.1rem; border-bottom: 0px;">Risk Scoring Configuration</h5>
                <p class="text-muted small mb-4">
                    Configure the scoring factors below. Adjust percentages using the steppers. All weights must combine to exactly 100% before adjustments can be saved.
                </p>

                <form action="{{ route('admin.weights.update') }}" method="POST" id="formWeightsIndex">
                    @csrf
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
                        <div class="category-weight-row card border shadow-none p-3.5 mb-0" style="border-radius: 12px; background-color: #FFFFFF; border-color: #E5E7EB !important;">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div style="flex: 1; min-width: 250px;">
                                    <h6 class="mb-1 fw-bold text-dark fs-6">{{ $w->riskCategory->name }}</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.4;">{{ $w->description }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button class="btn btn-outline-secondary btn-decrement d-flex align-items-center justify-content-center p-0" type="button" data-category-id="{{ $w->risk_category_id }}" style="width: 36px; height: 36px; border-radius: 10px; font-weight: 700; color: #475569; border-color: #CBD5E1 !important;">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <div class="position-relative" style="width: 75px;">
                                        <input type="number" class="form-control text-center weight-number-input p-0 fw-bold" name="weights[{{ $w->risk_category_id }}]" value="{{ $percent }}" min="0" max="100" data-category-id="{{ $w->risk_category_id }}" data-category-slug="{{ $w->riskCategory->slug }}" data-category-name="{{ $w->riskCategory->name }}" style="height: 36px; border-radius: 10px; border-color: #CBD5E1 !important; color: #1E293B !important; font-size: 1rem; padding-right: 18px !important;">
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-2.5 text-muted fw-semibold small" style="pointer-events: none; font-size: 0.85rem;">%</span>
                                    </div>
                                    <button class="btn btn-outline-secondary btn-increment d-flex align-items-center justify-content-center p-0" type="button" data-category-id="{{ $w->risk_category_id }}" style="width: 36px; height: 36px; border-radius: 10px; font-weight: 700; color: #475569; border-color: #CBD5E1 !important;">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="progress mt-3" style="height: 5px; background-color: #F1F5F9; border-radius: 9999px; overflow: hidden;">
                                <div class="progress-bar {{ $colorClass }}" role="progressbar" style="width: {{ $percent }}%; transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100" data-category-id="{{ $w->risk_category_id }}"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top" style="border-color: #F1F5F9 !important;">
                        <button type="button" class="btn px-4 py-2 fw-semibold btn-outline-secondary" id="btnResetDefaults" style="border-radius: 10px; font-size: 0.875rem;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Default
                        </button>
                        <button type="submit" class="btn px-4 py-2 fw-semibold btn-primary" id="btnSubmitIndexWeights" style="border-radius: 10px; font-size: 0.875rem;">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const numberInputs = document.querySelectorAll('.weight-number-input');
    const totalIndicator = document.getElementById('totalAllocationIndicator');
    const donutTotalIndicator = document.getElementById('donutTotalIndicator');
    const validationBanner = document.getElementById('validationBanner');
    const validationIcon = document.getElementById('validationIcon');
    const validationTitle = document.getElementById('validationTitle');
    const validationText = document.getElementById('validationStatusText');
    const btnSubmit = document.getElementById('btnSubmitIndexWeights');
    const btnReset = document.getElementById('btnResetDefaults');
    const formulaDetails = document.getElementById('formulaDetails');

    // Default values mapping (from seeder defaults)
    const defaults = {
        'economic-risk': 25,
        'weather-risk': 15,
        'geopolitical-risk': 25,
        'logistics-risk': 15,
        'currency-stability-risk': 20
    };

    // Category colors
    const colors = [
        '#EF4444', // Economic (danger)
        '#F59E0B', // Weather (warning)
        '#2563EB', // Geopolitical (primary)
        '#06B6D4', // Logistics (info)
        '#10B981'  // Currency (success)
    ];

    // Initialize Chart.js
    const ctx = document.getElementById('weightsDoughnutChart').getContext('2d');
    const initialData = Array.from(numberInputs).map(input => parseFloat(input.value || 0));
    const initialLabels = Array.from(numberInputs).map(input => input.getAttribute('data-category-name'));
    
    const weightsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: initialLabels,
            datasets: [{
                data: initialData,
                backgroundColor: colors,
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            cutout: '75%',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` ${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });

    // Steppers handler
    document.querySelectorAll('.btn-decrement').forEach(btn => {
        btn.addEventListener('click', function() {
            const catId = this.getAttribute('data-category-id');
            const input = document.querySelector(`.weight-number-input[data-category-id="${catId}"]`);
            if (input) {
                const newVal = Math.max(0, parseInt(input.value || 0) - 5);
                input.value = newVal;
                // trigger input event
                input.dispatchEvent(new Event('input'));
            }
        });
    });

    document.querySelectorAll('.btn-increment').forEach(btn => {
        btn.addEventListener('click', function() {
            const catId = this.getAttribute('data-category-id');
            const input = document.querySelector(`.weight-number-input[data-category-id="${catId}"]`);
            if (input) {
                const newVal = Math.min(100, parseInt(input.value || 0) + 5);
                input.value = newVal;
                // trigger input event
                input.dispatchEvent(new Event('input'));
            }
        });
    });

    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            const catId = this.getAttribute('data-category-id');
            const val = Math.min(100, Math.max(0, parseInt(this.value || 0)));
            this.value = val;

            // Sync progress bar
            const progBar = document.querySelector(`.progress-bar[data-category-id="${catId}"]`);
            if (progBar) {
                progBar.style.width = val + '%';
                progBar.setAttribute('aria-valuenow', val);
            }

            // Update legend percentage
            const legendEl = document.getElementById(`legendPercent-${catId}`);
            if (legendEl) {
                legendEl.innerText = val + '%';
            }

            calculateTotals();
        });
    });

    // Reset Defaults handler
    btnReset.addEventListener('click', function() {
        numberInputs.forEach(input => {
            const slug = input.getAttribute('data-category-slug');
            const catId = input.getAttribute('data-category-id');
            const defaultVal = defaults[slug] !== undefined ? defaults[slug] : 20;
            
            input.value = defaultVal;
            const progBar = document.querySelector(`.progress-bar[data-category-id="${catId}"]`);
            if (progBar) {
                progBar.style.width = defaultVal + '%';
                progBar.setAttribute('aria-valuenow', defaultVal);
            }

            const legendEl = document.getElementById(`legendPercent-${catId}`);
            if (legendEl) {
                legendEl.innerText = defaultVal + '%';
            }
        });
        calculateTotals();
    });

    function calculateTotals() {
        let total = 0;
        const chartData = [];
        const formulaParts = [];

        numberInputs.forEach(input => {
            const val = parseInt(input.value || 0);
            total += val;
            chartData.push(val);

            const name = input.getAttribute('data-category-name').replace(' Risk', '');
            formulaParts.push(`${name} × ${val}%`);
        });

        // Update indicators
        totalIndicator.innerText = total + '%';
        donutTotalIndicator.innerText = total + '%';

        // Update formula text
        formulaDetails.innerHTML = formulaParts.join('<br>+ ');

        // Update chart data
        weightsChart.data.datasets[0].data = chartData;
        weightsChart.update();

        // Validation banner toggling
        if (total === 100) {
            validationBanner.className = "px-3 py-2 rounded-pill fw-semibold small d-flex align-items-center gap-1.5 shadow-sm text-success border border-success border-opacity-20";
            validationBanner.style.backgroundColor = "rgba(16, 185, 129, 0.1)";
            validationIcon.className = "bi bi-check-circle-fill text-success fs-7";
            validationTitle.innerText = "Ready to Save";
            btnSubmit.disabled = false;
        } else {
            validationBanner.className = "px-3 py-2 rounded-pill fw-semibold small d-flex align-items-center gap-1.5 shadow-sm text-warning border border-warning border-opacity-20";
            validationBanner.style.backgroundColor = "rgba(245, 158, 11, 0.1)";
            validationIcon.className = "bi bi-exclamation-triangle-fill text-warning fs-7";
            validationTitle.innerText = "Total weight must equal 100%";
            btnSubmit.disabled = true;
        }
    }

    calculateTotals(); // run on page load init
});
</script>
@endpush
@endsection
