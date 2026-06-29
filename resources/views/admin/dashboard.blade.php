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
            value="128" 
            change="+4 new users" 
            changeType="up" 
            icon="bi-people" 
            iconColor="primary" 
        />
    </div>
    <div class="col-sm-6 col-xl-3">
        <x-stat-card 
            title="API Calls (Last 24h)" 
            value="1,420" 
            change="99.8% Success Rate" 
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
            value="340ms" 
            change="-12ms drop" 
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
                <button class="btn btn-sm btn-outline-primary py-1 px-3 fs-7">Diagnose All</button>
            </x-slot>
            
            <x-table :headers="['API Service Provider', 'Endpoint Base', 'Avg Latency', 'Status', 'Last Checked']">
                <tr>
                    <td><strong>Open-Meteo</strong></td>
                    <td class="small text-muted">/v1/forecast</td>
                    <td>145ms</td>
                    <td><x-badge type="success">Active (200)</x-badge></td>
                    <td class="small text-muted">10:00 AM</td>
                </tr>
                <tr>
                    <td><strong>World Bank API</strong></td>
                    <td class="small text-muted">/v2/country</td>
                    <td>420ms</td>
                    <td><x-badge type="success">Active (200)</x-badge></td>
                    <td class="small text-muted">10:00 AM</td>
                </tr>
                <tr>
                    <td><strong>REST Countries</strong></td>
                    <td class="small text-muted">/v3.1/all</td>
                    <td>280ms</td>
                    <td><x-badge type="success">Active (200)</x-badge></td>
                    <td class="small text-muted">10:00 AM</td>
                </tr>
                <tr>
                    <td><strong>ExchangeRate API</strong></td>
                    <td class="small text-muted">/v4/latest</td>
                    <td>110ms</td>
                    <td><x-badge type="success">Active (200)</x-badge></td>
                    <td class="small text-muted">10:00 AM</td>
                </tr>
                <tr>
                    <td><strong>GNews API</strong></td>
                    <td class="small text-muted">/v4/search</td>
                    <td>190ms</td>
                    <td><x-badge type="warning">Rate Limited (429)</x-badge></td>
                    <td class="small text-muted">10:00 AM</td>
                </tr>
            </x-table>
        </x-card>
    </div>

    <!-- Algorithm Weights View Section -->
    <div class="col-lg-4">
        <x-card title="Composite Score Weights" icon="bi-sliders">
            <div class="d-flex flex-column gap-3">
                <div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Economic Risk</span>
                        <span class="text-white fw-semibold">25% (0.25)</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--color-border);">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Geopolitical Risk</span>
                        <span class="text-white fw-semibold">25% (0.25)</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--color-border);">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Currency Volatility Risk</span>
                        <span class="text-white fw-semibold">20% (0.20)</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--color-border);">
                        <div class="progress-bar bg-success" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Weather & Disasters</span>
                        <span class="text-white fw-semibold">15% (0.15)</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--color-border);">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Logistics & Port Congestion</span>
                        <span class="text-white fw-semibold">15% (0.15)</span>
                    </div>
                    <div class="progress" style="height: 6px; background-color: var(--color-border);">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 15%" aria-valuenow="15" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
                <div class="pt-2 border-top border-secondary border-opacity-10 d-grid">
                    <button class="btn btn-sm btn-secondary fw-semibold">
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
                    <td>60 minutes</td>
                </tr>
                <tr>
                    <td><code>cache_duration_news</code></td>
                    <td><x-badge type="info">Cache</x-badge></td>
                    <td>30 minutes</td>
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
                <div class="d-flex gap-2 align-items-start">
                    <div class="text-warning mt-0.5"><i class="bi bi-pencil-square"></i></div>
                    <div>
                        <span class="text-white small fw-semibold">Adjusted System Configurations</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">Key <code>risk_score_high_max</code> modified from 80.00 to 75.00.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Today, 11:20 AM &bull; IP: 127.0.0.1</span>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <div class="text-success mt-0.5"><i class="bi bi-person-check"></i></div>
                    <div>
                        <span class="text-white small fw-semibold">Activated User Account</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">Admin verified and activated operator account <code>procurement@gscrip.com</code>.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">Yesterday, 3:45 PM &bull; IP: 127.0.0.1</span>
                    </div>
                </div>
                <div class="d-flex gap-2 align-items-start">
                    <div class="text-danger mt-0.5"><i class="bi bi-trash"></i></div>
                    <div>
                        <span class="text-white small fw-semibold">Purged Log History</span>
                        <p class="text-muted small mb-0" style="font-size: 0.75rem;">Executed automatic database table cleanup for <code>api_logs</code> older than 3 months.</p>
                        <span class="text-muted small" style="font-size: 0.7rem;">28 Jun 2026, 12:00 AM &bull; System Command</span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
</div>
@endsection
