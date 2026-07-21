@extends('layouts.app')

@section('title', 'Manage Risk Scoring Weights')

@section('content')
<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Admin Console</a></li>
            <li class="breadcrumb-item active text-white" aria-current="page">Risk Weights</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="card card-premium border-0 shadow-sm">
                <div class="card-header border-bottom border-secondary border-opacity-10 py-3">
                    <h5 class="card-title text-white mb-0">
                        <i class="bi bi-sliders text-primary me-2"></i> Scoring Weights Allocation
                    </h5>
                </div>
                <div class="card-body py-4">
                    <p class="text-muted small mb-4">
                        Adjust the proportional weight of each risk category below. The scoring algorithm aggregates these values to compute overall composite country risk ratings. 
                        <strong>Note: The sum of all weights must equal exactly 100%.</strong>
                    </p>

                    <form action="{{ route('admin.weights.update') }}" method="POST" id="formWeightsIndex">
                        @csrf
                        <div class="d-flex flex-column gap-4">
                            @foreach($weights as $w)
                            @php
                                $percent = (float)$w->weight * 100;
                            @endphp
                            <div class="category-weight-row p-3 rounded border border-secondary border-opacity-10 bg-black bg-opacity-20">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <h6 class="text-white mb-0 fw-semibold">{{ $w->riskCategory->name }}</h6>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" step="1" min="0" max="100" class="form-control text-center bg-dark text-white border-secondary weight-number-input" data-category-id="{{ $w->risk_category_id }}" name="weights[{{ $w->risk_category_id }}]" value="{{ $percent }}" style="width: 75px; font-weight: 600; border-radius: 8px;">
                                        <span class="text-muted fw-bold">%</span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3">{{ $w->description }}</p>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-muted small">0%</span>
                                    <input type="range" class="form-range weight-range-slider flex-grow-1" min="0" max="100" step="1" value="{{ $percent }}" data-category-id="{{ $w->risk_category_id }}" style="cursor: pointer;">
                                    <span class="text-muted small">100%</span>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top border-secondary border-opacity-10">
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary px-4 py-2 fw-semibold">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold" id="btnSubmitIndexWeights">
                                <i class="bi bi-save me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Summary Panel Column -->
        <div class="col-lg-4">
            <div class="card card-premium border-0 shadow-sm mb-4">
                <div class="card-header border-bottom border-secondary border-opacity-10 py-3">
                    <h5 class="card-title text-white mb-0">
                        <i class="bi bi-pie-chart text-success me-2"></i> Weight Allocation Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-4 mb-3">
                        <div class="display-3 fw-bold mb-1" id="totalAllocationIndicator">100%</div>
                        <div class="text-muted small uppercase tracking-wider">Total Combined Weight</div>
                    </div>

                    <div class="p-3 rounded border border-secondary border-opacity-10 bg-black bg-opacity-20 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i id="validationStatusIcon" class="bi bi-check-circle-fill text-success fs-5"></i>
                            <span class="text-white small fw-bold" id="validationStatusTitle">Validation Successful</span>
                        </div>
                        <p class="text-muted small mb-0" id="validationStatusText">
                            The weights are allocated correctly and sum to exactly 100%. Saving changes will update the algorithms and trigger a background recalculation of all country risk ratings.
                        </p>
                    </div>

                    <div class="d-flex flex-column gap-3" id="allocationMiniBars">
                        <!-- Dynamically updated by JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rangeSliders = document.querySelectorAll('.weight-range-slider');
    const numberInputs = document.querySelectorAll('.weight-number-input');
    const totalIndicator = document.getElementById('totalAllocationIndicator');
    const validationIcon = document.getElementById('validationStatusIcon');
    const validationTitle = document.getElementById('validationStatusTitle');
    const validationText = document.getElementById('validationStatusText');
    const btnSubmit = document.getElementById('btnSubmitIndexWeights');
    const miniBarsContainer = document.getElementById('allocationMiniBars');

    // Sync sliders and number fields
    rangeSliders.forEach(slider => {
        slider.addEventListener('input', function() {
            const catId = this.getAttribute('data-category-id');
            const numInput = document.querySelector(`.weight-number-input[data-category-id="${catId}"]`);
            if (numInput) {
                numInput.value = this.value;
                calculateTotals();
            }
        });
    });

    numberInputs.forEach(input => {
        input.addEventListener('input', function() {
            const catId = input.getAttribute('data-category-id');
            const slider = document.querySelector(`.weight-range-slider[data-category-id="${catId}"]`);
            if (slider) {
                slider.value = this.value;
                calculateTotals();
            }
        });
    });

    function calculateTotals() {
        let total = 0;
        const categories = [];

        numberInputs.forEach(input => {
            const catId = input.getAttribute('data-category-id');
            const rowElement = input.closest('.category-weight-row');
            const name = rowElement ? rowElement.querySelector('h6').innerText : 'Category';
            const value = parseFloat(input.value || 0);
            total += value;
            categories.push({ id: catId, name: name, value: value });
        });

        totalIndicator.innerText = total + '%';

        // Update mini progress bars
        let miniBarsHtml = '';
        categories.forEach(cat => {
            const colorClass = cat.name.toLowerCase().includes('currency') ? 'bg-success' :
                               cat.name.toLowerCase().includes('weather') ? 'bg-warning' :
                               cat.name.toLowerCase().includes('logistics') ? 'bg-info' :
                               cat.name.toLowerCase().includes('economic') ? 'bg-danger' : 'bg-primary';
            miniBarsHtml += `
                <div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>${cat.name}</span>
                        <span class="text-white fw-semibold">${cat.value}%</span>
                    </div>
                    <div class="progress" style="height: 5px; background-color: rgba(255, 255, 255, 0.05);">
                        <div class="progress-bar ${colorClass}" role="progressbar" style="width: ${cat.value}%" aria-valuenow="${cat.value}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            `;
        });
        miniBarsContainer.innerHTML = miniBarsHtml;

        // Validation status text & toggle buttons
        if (total === 100) {
            totalIndicator.className = "display-3 fw-bold mb-1 text-success";
            validationIcon.className = "bi bi-check-circle-fill text-success fs-5";
            validationTitle.innerText = "Validation Successful";
            validationTitle.className = "text-success small fw-bold";
            validationText.innerText = "The weights are allocated correctly and sum to exactly 100%. Saving changes will update the algorithms and trigger a background recalculation of all country risk ratings.";
            btnSubmit.disabled = false;
        } else {
            totalIndicator.className = "display-3 fw-bold mb-1 text-danger";
            validationIcon.className = "bi bi-exclamation-circle-fill text-danger fs-5";
            validationTitle.innerText = "Validation Failed";
            validationTitle.className = "text-danger small fw-bold";
            validationText.innerText = `The sum of all scoring weights must equal exactly 100%. Currently, the total sum is ${total}%. Please adjust the sliders to fix the allocation.`;
            btnSubmit.disabled = true;
        }
    }

    calculateTotals();
});
</script>
@endsection
