@extends('layouts.app')

@section('title', 'Global Country Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 text-dark mb-1 fw-bold">Global Country Dashboard</h1>
            <p class="text-muted small mb-0">Overview of supply chain metrics, macro-economics, and real-time weather alerts</p>
        </div>
        <div class="text-end">
            <span class="badge bg-secondary border border-secondary border-opacity-30 py-2 px-3 fw-semibold">
                <i class="bi bi-clock me-1 text-primary"></i> Last Live Sync: 
                {{ $lastLiveUpdate ? \Carbon\Carbon::parse($lastLiveUpdate)->diffForHumans() : 'Never' }}
            </span>
        </div>
    </div>

    <!-- Statistics Cards Section -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <x-stat-card 
                title="Total Countries" 
                value="{{ $totalCountries }}" 
                change="All Configured" 
                changeType="neutral" 
                icon="bi-globe" 
                iconColor="primary" 
            />
        </div>
        <div class="col-sm-6 col-md-3">
            <x-stat-card 
                title="Average Global GDP" 
                value="${{ number_format($avgGdp / 1e9, 2) }}B" 
                change="World Bank Live" 
                changeType="neutral" 
                icon="bi-graph-up-arrow" 
                iconColor="success" 
            />
        </div>
        <div class="col-sm-6 col-md-3">
            <x-stat-card 
                title="Average Inflation Rate" 
                value="{{ number_format($avgInflation, 2) }}%" 
                change="CPI Indicators" 
                changeType="neutral" 
                icon="bi-percent" 
                iconColor="warning" 
            />
        </div>
        <div class="col-sm-6 col-md-3">
            <x-stat-card 
                title="Average Population" 
                value="{{ number_format($avgPopulation / 1e6, 2) }}M" 
                change="Macro demographics" 
                changeType="neutral" 
                icon="bi-people" 
                iconColor="info" 
            />
        </div>
    </div>

    <!-- Controls Panel: Search & Filters -->
    <div class="card card-premium border-0 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('countries.index') }}" method="GET" id="filter-form">
                <!-- Keep sorting parameters -->
                <input type="hidden" name="sort_by" value="{{ $filters['sort_by'] ?? 'name' }}">
                <input type="hidden" name="sort_dir" value="{{ $filters['sort_dir'] ?? 'asc' }}">

                <div class="row g-3">
                    <!-- Search Input -->
                    <div class="col-md-4 col-lg-3">
                        <label class="form-label text-muted small fw-semibold">Search Country</label>
                        <div class="input-group search-bar-modern">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search name, codes..." value="{{ $filters['search'] ?? '' }}">
                        </div>
                    </div>

                    <!-- Region Filter -->
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label text-muted small fw-semibold">Region</label>
                        <select name="region" class="form-select">
                            <option value="">All Regions</option>
                            <option value="Americas" {{ ($filters['region'] ?? '') === 'Americas' ? 'selected' : '' }}>Americas</option>
                            <option value="Asia" {{ ($filters['region'] ?? '') === 'Asia' ? 'selected' : '' }}>Asia</option>
                            <option value="Europe" {{ ($filters['region'] ?? '') === 'Europe' ? 'selected' : '' }}>Europe</option>
                            <option value="Africa" {{ ($filters['region'] ?? '') === 'Africa' ? 'selected' : '' }}>Africa</option>
                            <option value="Oceania" {{ ($filters['region'] ?? '') === 'Oceania' ? 'selected' : '' }}>Oceania</option>
                        </select>
                    </div>

                    <!-- Population Range Filter -->
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label text-muted small fw-semibold">Population</label>
                        <select name="population_range" class="form-select">
                            <option value="">All Populations</option>
                            <option value="small" {{ ($filters['population_range'] ?? '') === 'small' ? 'selected' : '' }}>&lt; 5M</option>
                            <option value="medium" {{ ($filters['population_range'] ?? '') === 'medium' ? 'selected' : '' }}>5M - 50M</option>
                            <option value="large" {{ ($filters['population_range'] ?? '') === 'large' ? 'selected' : '' }}>&gt; 50M</option>
                        </select>
                    </div>

                    <!-- GDP Range Filter -->
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label text-muted small fw-semibold">GDP Value</label>
                        <select name="gdp_range" class="form-select">
                            <option value="">All GDPs</option>
                            <option value="low" {{ ($filters['gdp_range'] ?? '') === 'low' ? 'selected' : '' }}>&lt; $10B</option>
                            <option value="medium" {{ ($filters['gdp_range'] ?? '') === 'medium' ? 'selected' : '' }}>$10B - $500B</option>
                            <option value="high" {{ ($filters['gdp_range'] ?? '') === 'high' ? 'selected' : '' }}>&gt; $500B</option>
                        </select>
                    </div>

                    <!-- Inflation Filter -->
                    <div class="col-sm-6 col-md-2">
                        <label class="form-label text-muted small fw-semibold">Inflation Rate</label>
                        <select name="inflation_range" class="form-select">
                            <option value="">All Inflations</option>
                            <option value="deflation" {{ ($filters['inflation_range'] ?? '') === 'deflation' ? 'selected' : '' }}>&lt; 0% (Deflation)</option>
                            <option value="low" {{ ($filters['inflation_range'] ?? '') === 'low' ? 'selected' : '' }}>0% - 3% (Low)</option>
                            <option value="moderate" {{ ($filters['inflation_range'] ?? '') === 'moderate' ? 'selected' : '' }}>3% - 10% (Moderate)</option>
                            <option value="high" {{ ($filters['inflation_range'] ?? '') === 'high' ? 'selected' : '' }}>&gt; 10% (High)</option>
                        </select>
                    </div>

                    <!-- Submit & Reset Action Buttons -->
                    <div class="col-md-2 col-lg-1 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary-saas w-100 d-flex justify-content-center align-items-center" style="height: 38px;"><i class="bi bi-funnel-fill"></i></button>
                        <a href="{{ route('countries.index') }}" class="btn btn-secondary-saas w-100 d-flex justify-content-center align-items-center text-decoration-none" style="height: 38px;"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Country Dashboard Table -->
    <div class="card card-premium border-0 mb-4">
        <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center py-3">
            <h5 class="card-title text-dark mb-0 fs-6 fw-semibold"><i class="bi bi-table me-2 text-primary"></i>Global Country Directory</h5>
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Sort By:</span>
                @php
                    $currentSort = $filters['sort_by'] ?? 'name';
                    $currentDir = $filters['sort_dir'] ?? 'asc';
                    $nextDir = $currentDir === 'asc' ? 'desc' : 'asc';
                @endphp
                <div class="btn-group btn-group-sm">
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => ($currentSort === 'name' ? $nextDir : 'asc')]) }}" class="btn-secondary-saas btn-sm text-decoration-none d-flex align-items-center gap-1 {{ $currentSort === 'name' ? 'btn-primary-saas text-white' : '' }}" style="border-radius: 8px 0 0 8px !important;">Nama</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'population', 'sort_dir' => ($currentSort === 'population' ? $nextDir : 'asc')]) }}" class="btn-secondary-saas btn-sm text-decoration-none d-flex align-items-center gap-1 {{ $currentSort === 'population' ? 'btn-primary-saas text-white' : '' }}" style="border-radius: 0 !important; border-left: none !important;">Population</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'gdp', 'sort_dir' => ($currentSort === 'gdp' ? $nextDir : 'asc')]) }}" class="btn-secondary-saas btn-sm text-decoration-none d-flex align-items-center gap-1 {{ $currentSort === 'gdp' ? 'btn-primary-saas text-white' : '' }}" style="border-radius: 0 !important; border-left: none !important;">GDP</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'gdp_capita', 'sort_dir' => ($currentSort === 'gdp_capita' ? $nextDir : 'asc')]) }}" class="btn-secondary-saas btn-sm text-decoration-none d-flex align-items-center gap-1 {{ $currentSort === 'gdp_capita' ? 'btn-primary-saas text-white' : '' }}" style="border-radius: 0 !important; border-left: none !important;">GDP/Capita</a>
                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'inflation', 'sort_dir' => ($currentSort === 'inflation' ? $nextDir : 'asc')]) }}" class="btn-secondary-saas btn-sm text-decoration-none d-flex align-items-center gap-1 {{ $currentSort === 'inflation' ? 'btn-primary-saas text-white' : '' }}" style="border-radius: 0 8px 8px 0 !important; border-left: none !important;">Inflation</a>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <x-table :headers="['Country', 'Population', 'GDP (Billions)', 'GDP/Capita', 'Inflation', 'Currency', 'Weather Snapshot', 'Risk Score']">
                @forelse($countries as $c)
                @php
                    $gdpRecord = $c->economicIndicators->firstWhere('indicator_code', 'NY.GDP.MKTP.CD');
                    $capitaRecord = $c->economicIndicators->firstWhere('indicator_code', 'NY.GDP.PCAP.CD');
                    $inflationRecord = $c->economicIndicators->firstWhere('indicator_code', 'FP.CPI.TOTL.ZG');

                    $weather = $c->latestWeather;
                    $temp = $weather ? number_format($weather->temperature, 1) . '°C' : 'N/A';
                    $weatherDesc = $weather ? $weather->weather_description : 'No Weather';
                @endphp
                <tr>
                    <td class="align-middle">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $c->flag_url }}" alt="{{ $c->name }} Flag" class="country-flag-premium" style="width: 42px; height: 28px; object-fit: cover;">
                            <div class="d-flex flex-column" style="line-height: 1.35;">
                                <a href="{{ route('countries.show', $c->iso2) }}" class="text-dark fw-bold text-decoration-none hover-primary mb-0.5">{{ $c->name }}</a>
                                <span class="text-muted small" style="font-size: 0.76rem;">{{ $c->iso3 }} &bull; {{ $c->region }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle text-dark small">{{ number_format($c->population) }}</td>
                    <td class="align-middle text-dark small">
                        {{ $gdpRecord ? '$' . number_format($gdpRecord->value / 1e9, 2) : 'N/A' }}
                    </td>
                    <td class="align-middle text-dark small">
                        {{ $capitaRecord ? '$' . number_format($capitaRecord->value, 2) : 'N/A' }}
                    </td>
                    <td class="align-middle small">
                        @if($inflationRecord)
                            <span class="{{ $inflationRecord->value > 5 ? 'text-danger' : ($inflationRecord->value < 0 ? 'text-warning' : 'text-success') }} fw-semibold">
                                {{ number_format($inflationRecord->value, 2) }}%
                            </span>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                    <td class="align-middle small">
                        <div class="d-flex flex-column" style="line-height: 1.2;">
                            <span class="text-dark fw-semibold">{{ $c->currency_code }}</span>
                            <span class="text-muted" style="font-size: 0.72rem;">{{ $c->currency_symbol }} - {{ $c->currency_name }}</span>
                        </div>
                    </td>
                    <td class="align-middle small">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="text-warning fw-semibold">{{ $temp }}</span>
                            <span class="text-muted fs-7" style="font-size: 0.75rem;">({{ $weatherDesc }})</span>
                        </div>
                    </td>
                    <td class="align-middle">
                        @if($c->latestRiskScore)
                            @php
                                $level = $c->latestRiskScore->risk_level;
                                $badgeType = 'success';
                                if ($level === 'high' || $level === 'critical') $badgeType = 'danger';
                                elseif ($level === 'medium') $badgeType = 'warning';
                            @endphp
                            <x-badge type="{{ $badgeType }}">
                                {{ number_format($c->latestRiskScore->composite_score, 1) }} ({{ ucfirst($level) }})
                            </x-badge>
                        @else
                            <span class="text-muted small">N/A</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <div class="empty-state-icon"><i class="bi bi-globe2"></i></div>
                        <h5 class="empty-state-title">No Countries Match Your Filter</h5>
                        <p class="empty-state-desc">Try clearing or tweaking your search queries and filter options above.</p>
                        <a href="{{ route('countries.index') }}" class="btn btn-primary-saas text-decoration-none">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
                        </a>
                    </td>
                </tr>
                @endforelse
            </x-table>
        </div>
        
        <!-- Pagination Section -->
        @if($countries->hasPages())
        <div class="card-footer bg-transparent border-top py-3 d-flex justify-content-between align-items-center" style="border-color: #E5E7EB !important;">
            <div class="text-muted small">
                Showing {{ $countries->firstItem() ?? 0 }} to {{ $countries->lastItem() ?? 0 }} of {{ $countries->total() }} countries
            </div>
            <div>
                {{ $countries->appends(request()->query())->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
