@extends('layouts.app')

@section('title', 'Country Comparison Engine')

@push('styles')
<style>
    /* Premium comparison styling */
    .compare-card {
        background-color: #FFFFFF !important;
        border: 1px solid #E5E7EB !important;
        border-radius: 24px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.01) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .compare-card:hover {
        box-shadow: 0 12px 20px -3px rgba(0, 0, 0, 0.05) !important;
    }

    .selected-country-card {
        background: #FFFFFF !important;
        border: 1px solid #E5E7EB !important;
        border-radius: 20px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.01) !important;
        padding: 1.25rem !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .selected-country-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05) !important;
        border-color: #CBD5E1 !important;
    }

    @keyframes float-icon {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-6px) rotate(5deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }

    .compare-glass-circle {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(229, 231, 235, 0.8);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.04);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: float-icon 3s ease-in-out infinite;
        transition: all 0.2s ease;
    }

    .btn-compare-saas {
        background: linear-gradient(135deg, #2563EB 0%, #06B6D4 100%) !important;
        border: none !important;
        color: #FFFFFF !important;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35) !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
        font-size: 0.9rem !important;
    }

    .btn-compare-saas:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45) !important;
    }

    .dropdown-options-list {
        border-radius: 12px !important;
        border-color: #E5E7EB !important;
    }

    .option-item-saas {
        padding: 0.6rem 1rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .option-item-saas:hover {
        background-color: #F8FAFC;
    }

    /* Comparison rows styling */
    .compare-row-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748B;
    }
</style>
@endpush

@section('content')
@php
    // Query directly to get coordinates/region and risk scores of all countries
    $countriesData = \App\Models\Country::with('latestRiskScore')
        ->orderBy('name')
        ->get(['id', 'iso2', 'iso3', 'name', 'flag_url', 'region']);

    $c1Scores = [0, 0, 0, 0, 0];
    $c2Scores = [0, 0, 0, 0, 0];
@endphp

<!-- Header Title -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 text-dark mb-1 fw-bold">Country Comparison Engine</h1>
        <p class="text-muted small mb-0">Select two countries to compare economic status, geopolitical risks, weather conditions, and forex indices side-by-side.</p>
    </div>
</div>

<!-- Selector Panel -->
<div class="card compare-card border-0 mb-4">
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            <!-- Country A Selector -->
            <div class="col-lg-5">
                <div class="p-3 bg-light rounded-4" style="min-height: 220px;">
                    <label class="form-label text-dark small fw-bold text-uppercase tracking-wider mb-2">Compare Country A</label>
                    
                    <!-- Searchable Input A -->
                    <div class="position-relative mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchA" class="form-control bg-white border-start-0 text-dark" placeholder="Search country A..." autocomplete="off" style="border-radius: 0 10px 10px 0;">
                        </div>
                        <div id="optionsA" class="dropdown-options-list d-none position-absolute w-100 bg-white border shadow-lg mt-1" style="max-height: 240px; overflow-y: auto; z-index: 1050;">
                            <!-- JS populated options -->
                        </div>
                    </div>

                    <!-- Country A Selection Card -->
                    <div id="cardA">
                        <!-- JS selection view -->
                    </div>
                </div>
            </div>

            <!-- VS Icon Section -->
            <div class="col-lg-2 d-flex justify-content-center align-items-center py-2">
                <div class="compare-glass-circle text-primary fs-3">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
            </div>

            <!-- Country B Selector -->
            <div class="col-lg-5">
                <div class="p-3 bg-light rounded-4" style="min-height: 220px;">
                    <label class="form-label text-dark small fw-bold text-uppercase tracking-wider mb-2">Compare Country B</label>
                    
                    <!-- Searchable Input B -->
                    <div class="position-relative mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchB" class="form-control bg-white border-start-0 text-dark" placeholder="Search country B..." autocomplete="off" style="border-radius: 0 10px 10px 0;">
                        </div>
                        <div id="optionsB" class="dropdown-options-list d-none position-absolute w-100 bg-white border shadow-lg mt-1" style="max-height: 240px; overflow-y: auto; z-index: 1050;">
                            <!-- JS populated options -->
                        </div>
                    </div>

                    <!-- Country B Selection Card -->
                    <div id="cardB">
                        <!-- JS selection view -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden submit form -->
        <div class="text-center mt-4 pt-2">
            <form method="GET" action="{{ route('compare.index') }}" id="compareForm">
                <input type="hidden" name="countries[]" id="inputA" value="{{ $selectedCodes[0] ?? '' }}">
                <input type="hidden" name="countries[]" id="inputB" value="{{ $selectedCodes[1] ?? '' }}">
                <button type="submit" id="btnCompareSubmit" class="btn btn-compare-saas px-5 py-3 rounded-pill fw-bold text-uppercase tracking-wide" style="min-width: 260px;">
                    <i class="bi bi-shuffle me-2"></i> Compare Countries
                </button>
            </form>
        </div>
    </div>
</div>

@if($comparisonData->count() >= 2)
    @if($comparisonData->count() === 2)
        @php
            $c1 = $comparisonData[0];
            $c2 = $comparisonData[1];
            
            // Executive summary values
            $score1 = $c1['risk_score'] ? $c1['risk_score']->composite_score : 50;
            $score2 = $c2['risk_score'] ? $c2['risk_score']->composite_score : 50;
            $riskDiff = abs($score1 - $score2);
            $saferCountry = $score1 < $score2 ? $c1['country']->name : $c2['country']->name;
            
            $gdp1 = $c1['gdp'] ?? 0;
            $gdp2 = $c2['gdp'] ?? 0;
            $gdpDiff = abs($gdp1 - $gdp2);
            $richerCountry = $gdp1 > $gdp2 ? $c1['country']->name : $c2['country']->name;

            // Radar Chart values
            $c1Details = $c1['risk_score'] ? $c1['risk_score']->details->keyBy('riskCategory.slug') : collect();
            $c2Details = $c2['risk_score'] ? $c2['risk_score']->details->keyBy('riskCategory.slug') : collect();
            
            $c1Scores = [
                $c1Details->get('economic-risk')?->category_score ?? 0,
                $c1Details->get('weather-risk')?->category_score ?? 0,
                $c1Details->get('currency-stability-risk')?->category_score ?? 0,
                $c1Details->get('geopolitical-risk')?->category_score ?? 0,
                $c1Details->get('logistics-risk')?->category_score ?? 0,
            ];
            $c2Scores = [
                $c2Details->get('economic-risk')?->category_score ?? 0,
                $c2Details->get('weather-risk')?->category_score ?? 0,
                $c2Details->get('currency-stability-risk')?->category_score ?? 0,
                $c2Details->get('geopolitical-risk')?->category_score ?? 0,
                $c2Details->get('logistics-risk')?->category_score ?? 0,
            ];
        @endphp

        <!-- Sticky Compare Summary Bar -->
        <div id="stickyCompareSummary" class="d-none position-fixed top-0 start-50 translate-middle-x bg-white border shadow-lg py-2.5 px-4 rounded-pill align-items-center gap-3" style="z-index: 2000; margin-top: 86px; border-color: #E5E7EB !important; min-width: 320px;">
            <div class="d-flex align-items-center gap-2 small fw-bold text-dark">
                <img src="{{ $c1['country']->flag_url }}" style="width: 20px; height: 13px; object-fit: cover;" class="rounded border" alt="">
                <span>{{ $c1['country']->iso3 }}</span>
                <span class="badge bg-{{ $score1 > 55 ? 'danger' : ($score1 > 35 ? 'warning' : 'success') }}">{{ number_format($score1, 1) }}</span>
            </div>
            <span class="text-muted fw-bold">&harr;</span>
            <div class="d-flex align-items-center gap-2 small fw-bold text-dark">
                <img src="{{ $c2['country']->flag_url }}" style="width: 20px; height: 13px; object-fit: cover;" class="rounded border" alt="">
                <span>{{ $c2['country']->iso3 }}</span>
                <span class="badge bg-{{ $score2 > 55 ? 'danger' : ($score2 > 35 ? 'warning' : 'success') }}">{{ number_format($score2, 1) }}</span>
            </div>
            <a href="#compareForm" class="btn btn-primary btn-sm rounded-pill px-3 py-1 font-size-xs fw-semibold text-white text-decoration-none" style="font-size: 0.72rem;">New Compare</a>
        </div>

        <!-- Executive summary card -->
        <div class="alert bg-white border border-secondary border-opacity-10 mb-4 rounded-4 p-4 shadow-sm" style="border-left: 4px solid #2563EB !important;">
            <h6 class="text-primary fw-bold mb-2"><i class="bi bi-info-circle-fill me-2"></i>Executive Summary</h6>
            <p class="text-dark small mb-0" style="line-height: 1.6;">
                <strong>{{ $saferCountry }}</strong> exhibits a lower composite supply chain risk rating (difference of {{ number_format($riskDiff, 1) }} points) compared to its counterpart. 
                On the economic scale, <strong>{{ $richerCountry }}</strong> holds a larger market share with a GDP difference of ${{ number_format($gdpDiff / 1e9, 2) }} Billion USD.
            </p>
        </div>

        <!-- Premium Dual comparison layout -->
        <div class="card compare-card border-0 mb-4 shadow-sm">
            <div class="card-body p-4">
                <h5 class="text-dark fw-bold mb-4"><i class="bi bi-bar-chart-steps me-2 text-primary"></i>Comparison Metrics</h5>
                
                <!-- Header row -->
                <div class="row align-items-center py-3 border-bottom text-muted small fw-bold text-uppercase tracking-wider" style="background-color: #F8FAFC; border-radius: 12px 12px 0 0;">
                    <div class="col-5 text-start ps-3">
                        <img src="{{ $c1['country']->flag_url }}" class="rounded me-2 border" style="width: 24px; height: 16px; object-fit: cover;" alt="">
                        {{ $c1['country']->name }}
                    </div>
                    <div class="col-2 text-center text-dark tracking-widest">Vs</div>
                    <div class="col-5 text-end pe-3">
                        {{ $c2['country']->name }}
                        <img src="{{ $c2['country']->flag_url }}" class="rounded ms-2 border" style="width: 24px; height: 16px; object-fit: cover;" alt="">
                    </div>
                </div>

                <!-- Composite Risk Score row -->
                <div class="row align-items-center py-3.5 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="col-5 text-start ps-3">
                        @if($c1['risk_score'])
                            @php
                                $level = $c1['risk_score']->risk_level;
                                $badge = $level === 'high' || $level === 'critical' ? 'danger' : ($level === 'medium' ? 'warning' : 'success');
                            @endphp
                            <span class="fs-5 fw-bold text-dark me-2">{{ number_format($c1['risk_score']->composite_score, 1) }}</span>
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($level) }}</span>
                            @if($score1 < $score2)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 ms-2" style="font-size: 0.68rem;"><i class="bi bi-trophy-fill me-1"></i>LOWER RISK</span>
                            @endif
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                    <div class="col-2 text-center compare-row-label">Composite Risk</div>
                    <div class="col-5 text-end pe-3">
                        @if($c2['risk_score'])
                            @php
                                $level = $c2['risk_score']->risk_level;
                                $badge = $level === 'high' || $level === 'critical' ? 'danger' : ($level === 'medium' ? 'warning' : 'success');
                            @endphp
                            @if($score2 < $score1)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 me-2" style="font-size: 0.68rem;"><i class="bi bi-trophy-fill me-1"></i>LOWER RISK</span>
                            @endif
                            <span class="badge bg-{{ $badge }}">{{ ucfirst($level) }}</span>
                            <span class="fs-5 fw-bold text-dark ms-2">{{ number_format($c2['risk_score']->composite_score, 1) }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <!-- GDP row -->
                <div class="row align-items-center py-3.5 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="col-5 text-start ps-3">
                        @if($c1['gdp'])
                            <span class="fw-semibold text-dark">${{ number_format($c1['gdp'] / 1e9, 2) }}B</span>
                            @if($gdp1 > $gdp2)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 ms-2" style="font-size: 0.68rem;"><i class="bi bi-trophy-fill me-1"></i>LARGER GDP</span>
                            @endif
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                    <div class="col-2 text-center compare-row-label">GDP (Billion USD)</div>
                    <div class="col-5 text-end pe-3">
                        @if($c2['gdp'])
                            @if($gdp2 > $gdp1)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 me-2" style="font-size: 0.68rem;"><i class="bi bi-trophy-fill me-1"></i>LARGER GDP</span>
                            @endif
                            <span class="fw-semibold text-dark">${{ number_format($c2['gdp'] / 1e9, 2) }}B</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <!-- Inflation row -->
                <div class="row align-items-center py-3.5 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="col-5 text-start ps-3">
                        @if($c1['inflation'])
                            @php
                                $c1Dev = abs($c1['inflation'] - 2.0);
                                $c2Dev = $c2['inflation'] ? abs($c2['inflation'] - 2.0) : 999;
                            @endphp
                            <span class="fw-semibold {{ $c1['inflation'] > 5 ? 'text-danger' : 'text-success' }}">{{ number_format($c1['inflation'], 2) }}%</span>
                            @if($c1Dev < $c2Dev)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 ms-2" style="font-size: 0.68rem;"><i class="bi bi-check-circle-fill me-1"></i>STABLE RATE</span>
                            @endif
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                    <div class="col-2 text-center compare-row-label">Inflation (%)</div>
                    <div class="col-5 text-end pe-3">
                        @if($c2['inflation'])
                            @php
                                $c2Dev = abs($c2['inflation'] - 2.0);
                                $c1Dev = $c1['inflation'] ? abs($c1['inflation'] - 2.0) : 999;
                            @endphp
                            @if($c2Dev < $c1Dev)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 me-2" style="font-size: 0.68rem;"><i class="bi bi-check-circle-fill me-1"></i>STABLE RATE</span>
                            @endif
                            <span class="fw-semibold {{ $c2['inflation'] > 5 ? 'text-danger' : 'text-success' }}">{{ number_format($c2['inflation'], 2) }}%</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <!-- Currency Exchange row -->
                <div class="row align-items-center py-3.5 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="col-5 text-start ps-3">
                        @if($c1['exchange_rate'] && (float)$c1['exchange_rate']->rate_to_usd > 0)
                            <span class="fw-semibold text-dark">{{ number_format(1 / (float)$c1['exchange_rate']->rate_to_usd, 4) }} {{ $c1['exchange_rate']->currency_code }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                    <div class="col-2 text-center compare-row-label">Exchange Rate (1 USD)</div>
                    <div class="col-5 text-end pe-3">
                        @if($c2['exchange_rate'] && (float)$c2['exchange_rate']->rate_to_usd > 0)
                            <span class="fw-semibold text-dark">{{ number_format(1 / (float)$c2['exchange_rate']->rate_to_usd, 4) }} {{ $c2['exchange_rate']->currency_code }}</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <!-- Weather Conditions row -->
                <div class="row align-items-center py-3.5 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="col-5 text-start ps-3">
                        @if($c1['weather'])
                            <span class="fw-semibold text-dark">{{ $c1['weather']->temperature }}°C</span>
                            <span class="text-muted small">({{ $c1['weather']->weather_description }})</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                    <div class="col-2 text-center compare-row-label">Weather</div>
                    <div class="col-5 text-end pe-3">
                        @if($c2['weather'])
                            <span class="text-muted small">({{ $c2['weather']->weather_description }})</span>
                            <span class="fw-semibold text-dark">{{ $c2['weather']->temperature }}°C</span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>

                <!-- Population row -->
                <div class="row align-items-center py-3.5 border-bottom" style="border-color: #F1F5F9 !important;">
                    <div class="col-5 text-start ps-3">
                        <span class="fw-semibold text-dark">{{ number_format($c1['population']) }}</span>
                    </div>
                    <div class="col-2 text-center compare-row-label">Population</div>
                    <div class="col-5 text-end pe-3">
                        <span class="fw-semibold text-dark">{{ number_format($c2['population']) }}</span>
                    </div>
                </div>

                <!-- Ports Count row -->
                <div class="row align-items-center py-3.5" style="border-color: #F1F5F9 !important;">
                    <div class="col-5 text-start ps-3">
                        @php
                            $ports1 = $c1['country']->ports->count();
                            $ports2 = $c2['country']->ports->count();
                        @endphp
                        <span class="fw-semibold text-dark">{{ $ports1 }} Ports</span>
                        @if($ports1 > $ports2)
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 ms-2" style="font-size: 0.68rem;"><i class="bi bi-anchor me-1"></i>LARGER HUB</span>
                        @endif
                    </div>
                    <div class="col-2 text-center compare-row-label">Ports Count</div>
                    <div class="col-5 text-end pe-3">
                        @php
                            $ports2 = $c2['country']->ports->count();
                            $ports1 = $c1['country']->ports->count();
                        @endphp
                        @if($ports2 > $ports1)
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 me-2" style="font-size: 0.68rem;"><i class="bi bi-anchor me-1"></i>LARGER HUB</span>
                        @endif
                        <span class="fw-semibold text-dark">{{ $ports2 }} Ports</span>
                    </div>
                </div>
            </div>
            
            <div class="card-footer bg-transparent border-top p-3 d-flex gap-3">
                <a href="{{ route('countries.show', $c1['country']->iso2) }}" class="btn btn-secondary-saas w-50 text-center text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i> Profile {{ $c1['country']->name }}
                </a>
                <a href="{{ route('countries.show', $c2['country']->iso2) }}" class="btn btn-secondary-saas w-50 text-center text-decoration-none">
                    Profile {{ $c2['country']->name }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    @else
        <!-- Multi-country fallback comparison list -->
        <div class="row g-4 mb-4">
            @foreach($comparisonData as $item)
            <div class="col-md-{{ 12 / min($comparisonData->count(), 4) }}">
                <div class="card compare-card h-100">
                    <div class="card-header bg-transparent border-bottom py-3" style="border-color: #E5E7EB !important;">
                        <div class="d-flex align-items-center gap-2">
                            <img src="{{ $item['country']->flag_url }}" alt="{{ $item['country']->name }}" class="rounded" style="width: 28px; height: 18px; object-fit: cover;">
                            <h5 class="text-dark mb-0 fs-6 fw-bold">{{ $item['country']->name }}</h5>
                            <span class="badge bg-light text-muted border small ms-auto">{{ $item['country']->iso3 }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <div class="compare-row-label mb-1">GDP</div>
                                <div class="fw-bold text-dark">{{ $item['gdp'] ? '$' . number_format((float)$item['gdp'] / 1e9, 2) . 'B' : 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="compare-row-label mb-1">Inflation</div>
                                <div class="fw-bold {{ $item['inflation'] && (float)$item['inflation'] > 5 ? 'text-danger' : 'text-success' }}">
                                    {{ $item['inflation'] ? number_format((float)$item['inflation'], 2) . '%' : 'N/A' }}
                                </div>
                            </div>
                            <div>
                                <div class="compare-row-label mb-1">Population</div>
                                <div class="fw-bold text-dark">{{ number_format($item['population']) }}</div>
                            </div>
                            <div>
                                <div class="compare-row-label mb-1">Composite Risk</div>
                                @if($item['risk_score'])
                                    @php
                                        $rs = $item['risk_score'];
                                        $badgeType = $rs->risk_level === 'high' || $rs->risk_level === 'critical' ? 'danger' : ($rs->risk_level === 'medium' ? 'warning' : 'success');
                                    @endphp
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-dark fs-5">{{ number_format($rs->composite_score, 2) }}</span>
                                        <x-badge type="{{ $badgeType }}">{{ ucfirst($rs->risk_level) }}</x-badge>
                                    </div>
                                @else
                                    <span class="text-muted">Not calculated</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top py-2">
                        <a href="{{ route('countries.show', $item['country']->iso2) }}" class="btn btn-sm btn-outline-primary w-100 fw-semibold">
                            View Full Profile
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        @if($comparisonData->count() === 2)
        <div class="col-lg-4">
            <x-card title="Risk Parameter Comparison" icon="bi-shield-check">
                <div style="height: 280px; position: relative;">
                    <canvas id="compareRadarChart" height="280"></canvas>
                </div>
            </x-card>
        </div>
        <div class="col-lg-4">
            <x-card title="GDP Trend (Billion USD)" icon="bi-graph-up">
                <div style="height: 280px; position: relative;">
                    <canvas id="gdpTrendChart" height="280"></canvas>
                </div>
            </x-card>
        </div>
        <div class="col-lg-4">
            <x-card title="Inflation Trend (%)" icon="bi-graph-down">
                <div style="height: 280px; position: relative;">
                    <canvas id="inflationTrendChart" height="280"></canvas>
                </div>
            </x-card>
        </div>
        @else
        <div class="col-lg-6">
            <x-card title="GDP Trend Comparison (Billion USD)" icon="bi-graph-up">
                <div style="height: 280px; position: relative;">
                    <canvas id="gdpTrendChart" height="280"></canvas>
                </div>
            </x-card>
        </div>
        <div class="col-lg-6">
            <x-card title="Inflation Trend Comparison (%)" icon="bi-graph-down">
                <div style="height: 280px; position: relative;">
                    <canvas id="inflationTrendChart" height="280"></canvas>
                </div>
            </x-card>
        </div>
        @endif
    </div>
@else
    <!-- Empty State illustration and details -->
    <div class="card compare-card border-0">
        <div class="card-body text-center py-5">
            <div class="display-5 text-muted mb-3 opacity-50"><i class="bi bi-shuffle"></i></div>
            <h5 class="text-dark fw-bold mb-2">Select Two Countries</h5>
            <p class="text-muted small mx-auto mb-0" style="max-width: 380px;">Choose one country on the left and another on the right to begin a detailed comparison.</p>
        </div>
    </div>
@endif
@endsection

@php
    $formattedCountries = $countriesData->map(function($c) {
        return [
            'id' => $c->id,
            'iso2' => $c->iso2,
            'iso3' => $c->iso3,
            'name' => $c->name,
            'flag_url' => $c->flag_url,
            'region' => $c->region,
            'score' => $c->latestRiskScore ? (float)$c->latestRiskScore->composite_score : null,
            'level' => $c->latestRiskScore ? $c->latestRiskScore->risk_level : 'N/A'
        ];
    });
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Countries data array from Blade
    const countriesList = @json($formattedCountries);

    // Selection handlers
    const selected = {
        A: null,
        B: null
    };

    // Prepopulate if backend selection exists
    const inputAVal = document.getElementById('inputA').value;
    const inputBVal = document.getElementById('inputB').value;

    console.log("Compare Page Prepopulate check: inputA =", inputAVal, "inputB =", inputBVal);
    console.log("Compare Page countriesList count:", countriesList.length);

    if (inputAVal) {
        const found = countriesList.find(c => c.iso2.toUpperCase() === inputAVal.toUpperCase());
        console.log("Found Country A in prepopulate:", found);
        if (found) selectCountry('A', found);
    } else {
        renderSelectedCard('A', null);
    }

    if (inputBVal) {
        const found = countriesList.find(c => c.iso2.toUpperCase() === inputBVal.toUpperCase());
        console.log("Found Country B in prepopulate:", found);
        if (found) selectCountry('B', found);
    } else {
        renderSelectedCard('B', null);
    }

    // Dropdown search triggers
    setupSearch('A');
    setupSearch('B');

    function setupSearch(side) {
        const searchInput = document.getElementById('search' + side);
        const optionsDiv = document.getElementById('options' + side);

        searchInput.addEventListener('focus', function() {
            renderOptions(side, searchInput.value);
        });

        searchInput.addEventListener('input', function() {
            renderOptions(side, searchInput.value);
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !optionsDiv.contains(e.target)) {
                optionsDiv.classList.add('d-none');
            }
        });
    }

    function renderOptions(side, query) {
        const optionsDiv = document.getElementById('options' + side);
        optionsDiv.innerHTML = '';
        
        const filtered = countriesList.filter(c => 
            c.name.toLowerCase().includes(query.toLowerCase()) ||
            c.iso2.toLowerCase().includes(query.toLowerCase()) ||
            c.iso3.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 10);

        if (filtered.length === 0) {
            optionsDiv.innerHTML = '<div class="p-3 text-muted small text-center">No results found</div>';
            optionsDiv.classList.remove('d-none');
            return;
        }

        filtered.forEach(country => {
            const div = document.createElement('div');
            div.className = 'option-item-saas d-flex align-items-center gap-2 small text-dark';
            div.innerHTML = `
                <img src="${country.flag_url}" class="rounded border" style="width: 20px; height: 13px; object-fit: cover;" alt="">
                <span>${country.name} (${country.iso3})</span>
            `;
            div.addEventListener('click', function() {
                selectCountry(side, country);
                optionsDiv.classList.add('d-none');
            });
            optionsDiv.appendChild(div);
        });

        optionsDiv.classList.remove('d-none');
    }

    function selectCountry(side, country) {
        console.log("selectCountry called for side", side, "country:", country);
        selected[side] = country;
        document.getElementById('input' + side).value = country ? country.iso2 : '';
        document.getElementById('search' + side).value = country ? country.name : '';
        renderSelectedCard(side, country);
    }

    function renderSelectedCard(side, country) {
        const cardEl = document.getElementById('card' + side);
        if (!country) {
            cardEl.innerHTML = `
                <div class="selected-country-card d-flex align-items-center justify-content-center text-muted p-4" style="border-style: dashed !important; border-width: 2px !important; background-color: #F8FAFC !important; min-height: 110px;">
                    <div class="text-center">
                        <i class="bi bi-globe display-6 d-block mb-1 text-muted opacity-40"></i>
                        <span class="small fw-semibold">No Country Selected</span>
                    </div>
                </div>
            `;
            return;
        }

        const badgeClass = country.level === 'high' || country.level === 'critical' ? 'bg-danger' : (country.level === 'medium' ? 'bg-warning' : 'bg-success');
        const dotColor = country.level === 'high' || country.level === 'critical' ? '#EF4444' : (country.level === 'medium' ? '#F59E0B' : '#10B981');
        const scoreVal = country.score ? country.score.toFixed(1) : 'N/A';

        cardEl.innerHTML = `
            <div class="selected-country-card">
                <div class="d-flex align-items-center gap-3">
                    <img src="${country.flag_url}" alt="${country.name}" class="rounded border" style="width: 48px; height: 32px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="text-dark mb-0 fw-bold fs-6">${country.name}</h5>
                            <span class="badge bg-light text-muted border small">${country.iso3}</span>
                        </div>
                        <span class="text-muted small">${country.region || 'Region N/A'}</span>
                    </div>
                    <div class="text-end">
                        <div class="d-flex align-items-center gap-1.5 justify-content-end mb-1">
                            <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: ${dotColor};"></span>
                            <span class="badge ${badgeClass} text-white">${country.level.toUpperCase()}</span>
                        </div>
                        <span class="small text-muted fw-bold">Score: ${scoreVal}</span>
                    </div>
                </div>
            </div>
        `;
    }

    // Swap button on compare icon
    const compareIconCircle = document.querySelector('.compare-glass-circle');
    if (compareIconCircle) {
        compareIconCircle.style.cursor = 'pointer';
        compareIconCircle.addEventListener('click', function(e) {
            console.log("Swap button clicked. Current selected:", selected);
            const temp = selected.A;
            selectCountry('A', selected.B);
            selectCountry('B', temp);
            console.log("Post-swap selected:", selected);
            if (selected.A && selected.B) {
                console.log("Submitting form from swap action");
                document.getElementById('compareForm').submit();
            }
        });
    }

    // Sticky Scroll bar
    const stickyBar = document.getElementById('stickyCompareSummary');
    if (stickyBar) {
        const scrollContainer = document.querySelector('.content-container') || window;
        scrollContainer.addEventListener('scroll', function() {
            const scrollTop = scrollContainer.scrollTop || window.scrollY || window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
            console.log("Scroll listener trigger: scrollTop =", scrollTop);
            if (scrollTop > 180) {
                stickyBar.classList.remove('d-none');
                stickyBar.classList.add('d-flex');
            } else {
                stickyBar.classList.add('d-none');
                stickyBar.classList.remove('d-flex');
            }
        });
        
        // Also listen on window as back up
        if (scrollContainer !== window) {
            window.addEventListener('scroll', function() {
                const scrollTop = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
                if (scrollTop > 180) {
                    stickyBar.classList.remove('d-none');
                    stickyBar.classList.add('d-flex');
                } else {
                    stickyBar.classList.add('d-none');
                    stickyBar.classList.remove('d-flex');
                }
            });
        }
    }

    // Chart.js Premium Styling overrides
    const chartColors = [
        'rgba(37, 99, 235, 1)', 'rgba(6, 182, 212, 1)', 'rgba(16, 185, 129, 1)',
        'rgba(249, 115, 22, 1)', 'rgba(168, 85, 247, 1)'
    ];
    const chartColorsBg = [
        'rgba(37, 99, 235, 0.08)', 'rgba(6, 182, 212, 0.08)', 'rgba(16, 185, 129, 0.08)',
        'rgba(249, 115, 22, 0.08)', 'rgba(168, 85, 247, 0.08)'
    ];

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#475569', font: { family: 'Outfit', size: 11, weight: '600' } } },
            tooltip: {
                backgroundColor: '#1E293B',
                titleColor: '#FFFFFF',
                bodyColor: '#F1F5F9',
                borderColor: '#475569',
                borderWidth: 1,
                borderRadius: 12,
                padding: 10,
                boxPadding: 6
            }
        },
        scales: {
            x: { 
                ticks: { color: '#64748B', font: { size: 10, weight: '600' } }, 
                grid: { display: false } 
            },
            y: { 
                ticks: { color: '#64748B', font: { size: 10 } }, 
                grid: { color: 'rgba(226, 232, 240, 0.8)' },
                border: { dash: [4, 4] }
            }
        }
    };

    // Radar Chart Comparison
    if (document.getElementById('compareRadarChart')) {
        new Chart(document.getElementById('compareRadarChart'), {
            type: 'radar',
            data: {
                labels: ['Economic', 'Weather', 'Currency', 'Geopolitical', 'Logistics'],
                datasets: [
                    {
                        label: @json($c1['country']->name ?? 'Country A'),
                        data: @json($c1Scores),
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
                        borderColor: '#2563EB',
                        borderWidth: 2,
                        pointBackgroundColor: '#2563EB',
                        pointBorderColor: '#FFFFFF',
                        pointHoverBackgroundColor: '#FFFFFF',
                        pointHoverBorderColor: '#2563EB'
                    },
                    {
                        label: @json($c2['country']->name ?? 'Country B'),
                        data: @json($c2Scores),
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderColor: '#10B981',
                        borderWidth: 2,
                        pointBackgroundColor: '#10B981',
                        pointBorderColor: '#FFFFFF',
                        pointHoverBackgroundColor: '#FFFFFF',
                        pointHoverBorderColor: '#10B981'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#475569', font: { family: 'Outfit', size: 10, weight: '600' } } }
                },
                scales: {
                    r: {
                        angleLines: { color: 'rgba(226, 232, 240, 0.6)' },
                        grid: { color: 'rgba(226, 232, 240, 0.6)' },
                        pointLabels: { color: '#64748B', font: { size: 9, weight: '600' } },
                        ticks: { display: false, maxTicksLimit: 5 }
                    }
                }
            }
        });
    }

    // GDP Trend
    const gdpTrends = @json($gdpTrends ?? []);
    if (gdpTrends.length > 0 && document.getElementById('gdpTrendChart')) {
        const allYears = [...new Set(gdpTrends.flatMap(t => t.years))].sort();
        new Chart(document.getElementById('gdpTrendChart'), {
            type: 'line',
            data: {
                labels: allYears,
                datasets: gdpTrends.map((t, i) => ({
                    label: t.label,
                    data: allYears.map(y => {
                        const idx = t.years.indexOf(y);
                        return idx >= 0 ? t.data[idx] : null;
                    }),
                    borderColor: chartColors[i % chartColors.length],
                    backgroundColor: chartColorsBg[i % chartColorsBg.length],
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 2.5,
                    pointHoverRadius: 5
                }))
            },
            options: defaultOptions
        });
    }

    // Inflation Trend
    const inflationTrends = @json($inflationTrends ?? []);
    if (inflationTrends.length > 0 && document.getElementById('inflationTrendChart')) {
        const allYears2 = [...new Set(inflationTrends.flatMap(t => t.years))].sort();
        new Chart(document.getElementById('inflationTrendChart'), {
            type: 'line',
            data: {
                labels: allYears2,
                datasets: inflationTrends.map((t, i) => ({
                    label: t.label,
                    data: allYears2.map(y => {
                        const idx = t.years.indexOf(y);
                        return idx >= 0 ? t.data[idx] : null;
                    }),
                    borderColor: chartColors[i % chartColors.length],
                    backgroundColor: chartColorsBg[i % chartColorsBg.length],
                    fill: true,
                    tension: 0.35,
                    borderWidth: 3,
                    pointRadius: 2.5,
                    pointHoverRadius: 5
                }))
            },
            options: defaultOptions
        });
    }
});
</script>
@endpush
