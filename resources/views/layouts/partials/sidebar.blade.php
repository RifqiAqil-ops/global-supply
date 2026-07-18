<!-- Sidebar Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar Layout Container -->
<div class="sidebar d-flex flex-column flex-shrink-0 vh-100 position-sticky top-0" id="sidebarMenu">
    <!-- Brand / Logo Area -->
    <div class="p-4 d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="28" height="28" fill="none" class="me-2" style="flex-shrink: 0;">
                <path d="M 24 8 L 16 4 L 8 8 L 4 16 L 8 24 L 16 28 L 24 24 L 24 16 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M 16 4 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M 4 16 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M 16 28 L 16 16" stroke="#2563EB" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" />
                <circle cx="24" cy="8" r="2.2" fill="#2563EB" />
                <circle cx="16" cy="4" r="2.2" fill="#2563EB" />
                <circle cx="8" cy="8" r="2.2" fill="#2563EB" />
                <circle cx="4" cy="16" r="2.2" fill="#2563EB" />
                <circle cx="8" cy="24" r="2.2" fill="#2563EB" />
                <circle cx="16" cy="28" r="2.2" fill="#2563EB" />
                <circle cx="24" cy="24" r="2.2" fill="#2563EB" />
                <circle cx="24" cy="16" r="2.2" fill="#2563EB" />
                <circle cx="16" cy="16" r="3.2" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.2" />
            </svg>
            <span class="text-primary fs-4 fw-extrabold" style="letter-spacing: 0.5px; font-family: 'Outfit', sans-serif;">
                GSCRIP
            </span>
        </a>
        <!-- Close button for mobile -->
        <button class="btn btn-sm text-muted d-lg-none p-0" id="sidebarCloseBtn">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Navigation Menu Items -->
    <div class="overflow-y-auto flex-grow-1 py-2">
        <ul class="nav nav-pills flex-column mb-auto">
            <!-- OVERVIEW SECTION -->
            <li class="sidebar-heading">
                OVERVIEW
            </li>
            <li>
                @php
                    $dashboardRoute = Auth::user()->isAdmin() ? 'admin.dashboard' : 'user.dashboard';
                @endphp
                <a href="{{ route($dashboardRoute) }}" class="nav-link {{ Route::is($dashboardRoute) ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <!-- MONITORING SECTION -->
            <li class="sidebar-heading">
                MONITORING
            </li>
            <li>
                <a href="{{ route('countries.index') }}" class="nav-link {{ Route::is('countries.index') ? 'active' : '' }}">
                    <i class="bi bi-flag"></i> Countries Index
                </a>
            </li>
            <li>
                <a href="{{ route('ports.index') }}" class="nav-link {{ Route::is('ports.index') ? 'active' : '' }}">
                    <i class="bi bi-anchor"></i> Ports & Logistics
                </a>
            </li>
            <li>
                <a href="{{ route('watchlists.index') }}" class="nav-link {{ Route::is('watchlists.index') ? 'active' : '' }}">
                    <i class="bi bi-eye"></i> Watchlists
                </a>
            </li>
            <li>
                <a href="{{ route('risk-history.index') }}" class="nav-link {{ Route::is('risk-history.index') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Risk History
                </a>
            </li>

            <!-- ANALYTICS SECTION -->
            <li class="sidebar-heading">
                ANALYTICS
            </li>
            <li>
                <a href="{{ route('compare.index') }}" class="nav-link {{ Route::is('compare.index') ? 'active' : '' }}">
                    <i class="bi bi-shuffle"></i> Country Compare
                </a>
            </li>
            <li>
                <a href="{{ route('currency.index') }}" class="nav-link {{ Route::is('currency.index') ? 'active' : '' }}">
                    <i class="bi bi-currency-exchange"></i> Currency Monitor
                </a>
            </li>
            <li>
                <a href="{{ route('weather.index') }}" class="nav-link {{ Route::is('weather.index') ? 'active' : '' }}">
                    <i class="bi bi-cloud-lightning"></i> Weather Alerts
                </a>
            </li>
            <li>
                <a href="{{ route('news.index') }}" class="nav-link {{ Route::is('news.index') ? 'active' : '' }}">
                    <i class="bi bi-newspaper"></i> Geopolitical News
                </a>
            </li>
            <li>
                <a href="{{ route('reports.index') }}" class="nav-link {{ Route::is('reports.index') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i> Sourcing Reports
                </a>
            </li>
            <li>
                <a href="{{ route('articles.index') }}" class="nav-link {{ Route::is('articles.index') || Route::is('articles.show') ? 'active' : '' }}">
                    <i class="bi bi-journal-richtext"></i> Analysis Reports
                </a>
            </li>

            <!-- ADMINISTRATION SECTION -->
            @if (Auth::user()->isAdmin())
                <li class="sidebar-heading">
                    ADMINISTRATION
                </li>
                <li>
                    <a href="{{ route('admin.users.index') }}" class="nav-link {{ Route::is('admin.users.index') || Route::is('admin.users.create') || Route::is('admin.users.edit') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> User Manager
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.ports.index') }}" class="nav-link {{ Route::is('admin.ports.index') || Route::is('admin.ports.create') || Route::is('admin.ports.edit') ? 'active' : '' }}">
                        <i class="bi bi-anchor"></i> Manage Ports
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.articles.index') }}" class="nav-link {{ Route::is('admin.articles.index') || Route::is('admin.articles.create') || Route::is('admin.articles.edit') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Manage Articles
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.weights.index') }}" class="nav-link {{ Route::is('admin.weights.index') ? 'active' : '' }}">
                        <i class="bi bi-sliders"></i> Risk Weights
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.api-health.index') }}" class="nav-link {{ Route::is('admin.api-health.index') ? 'active' : '' }}">
                        <i class="bi bi-cpu"></i> API Health
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.audit-trails.index') }}" class="nav-link {{ Route::is('admin.audit-trails.index') ? 'active' : '' }}">
                        <i class="bi bi-journal-text"></i> Audit Trails
                    </a>
                </li>
            @endif
        </ul>
    </div>

    <!-- User Profile Footer Area -->
    <div class="p-4 border-top mt-auto" style="border-color: #E5E7EB !important; background-color: #F8FAFC;">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 d-flex align-items-center justify-content-center fw-bold text-uppercase" style="width: 40px; height: 40px; font-size: 0.95rem; flex-shrink: 0;">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="overflow-hidden flex-grow-1" style="line-height: 1.25;">
                <div class="fw-bold text-dark text-truncate small">{{ Auth::user()->name }}</div>
                <span class="text-muted small" style="font-size: 0.72rem;">{{ ucfirst(Auth::user()->role) }}</span>
            </div>
            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-link text-muted hover-danger p-1 m-0 shadow-none border-0" title="Sign Out">
                    <i class="bi bi-power fs-5"></i>
                </button>
            </form>
        </div>
    </div>
</div>
