<!-- Sidebar Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar Layout Container -->
<div class="sidebar d-flex flex-column flex-shrink-0 vh-100 position-sticky top-0" id="sidebarMenu">
    <!-- Brand / Logo Area -->
    <div class="p-4 d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center">
            <img src="{{ asset('images/logo.png') }}" alt="Waypoint Logo" class="me-2" style="height: 28px; width: auto; flex-shrink: 0; object-fit: contain;">
            <span class="text-primary fs-4 fw-bold" style="letter-spacing: 0.5px; font-family: 'Inter', sans-serif;">
                Waypoint
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
