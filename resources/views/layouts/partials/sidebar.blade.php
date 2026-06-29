<!-- Sidebar Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar Layout Container -->
<div class="sidebar d-flex flex-column flex-shrink-0 vh-100 position-sticky top-0" id="sidebarMenu">
    <!-- Brand / Logo Area -->
    <div class="p-4 border-bottom d-flex align-items-center justify-content-between" style="border-color: var(--color-border) !important;">
        <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center gap-2">
            <span class="text-primary fs-4 fw-extrabold" style="letter-spacing: 1.5px; font-family: var(--font-header);">
                <i class="bi bi-globe-americas"></i> GSCRIP
            </span>
        </a>
        <!-- Close button for mobile -->
        <button class="btn btn-sm text-muted d-lg-none p-0" id="sidebarCloseBtn">
            <i class="bi bi-x-lg fs-5"></i>
        </button>
    </div>

    <!-- Navigation Menu Items -->
    <div class="overflow-y-auto flex-grow-1 py-3">
        <ul class="nav nav-pills flex-column mb-auto">
            <!-- GENERAL SECTION -->
            <li class="px-4 mb-2 text-muted text-uppercase small fw-bold" style="font-size: 0.65rem; letter-spacing: 1.5px;">
                Main
            </li>
            
            <li>
                @php
                    $dashboardRoute = Auth::user()->isAdmin() ? 'admin.dashboard' : 'user.dashboard';
                @endphp
                <a href="{{ route($dashboardRoute) }}" class="nav-link {{ Route::is($dashboardRoute) ? 'active' : '' }}">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </span>
                </a>
            </li>

            <!-- SOURCING & RISK SECTION (Collapsible menu mockup) -->
            <li class="px-4 mt-4 mb-2 text-muted text-uppercase small fw-bold" style="font-size: 0.65rem; letter-spacing: 1.5px;">
                Risk Intelligence
            </li>

            <li>
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#menuSourcing" role="button" aria-expanded="false" aria-controls="menuSourcing">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check"></i> Sourcing Monitor
                    </span>
                    <i class="bi bi-chevron-down small menu-arrow"></i>
                </a>
                <div class="collapse" id="menuSourcing">
                    <ul class="submenu">
                        <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-flag"></i> Countries Index</a></li>
                        <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-anchor"></i> Ports & Logistics</a></li>
                        <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-eye"></i> Watchlists</a></li>
                    </ul>
                </div>
            </li>

            <li>
                <a class="nav-link collapsed" data-bs-toggle="collapse" href="#menuAnalytics" role="button" aria-expanded="false" aria-controls="menuAnalytics">
                    <span class="d-flex align-items-center gap-2">
                        <i class="bi bi-bar-chart-line"></i> Risk Analytics
                    </span>
                    <i class="bi bi-chevron-down small menu-arrow"></i>
                </a>
                <div class="collapse" id="menuAnalytics">
                    <ul class="submenu">
                        <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-shuffle"></i> Country Compare</a></li>
                        <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-currency-exchange"></i> Currency Monitor</a></li>
                        <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-cloud-lightning"></i> Weather Alerts</a></li>
                        <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-newspaper"></i> Geopolitical News</a></li>
                    </ul>
                </div>
            </li>

            <!-- ADMIN SECTION -->
            @if (Auth::user()->isAdmin())
                <li class="px-4 mt-4 mb-2 text-muted text-uppercase small fw-bold" style="font-size: 0.65rem; letter-spacing: 1.5px;">
                    Administration
                </li>
                <li>
                    <a class="nav-link collapsed" data-bs-toggle="collapse" href="#menuAdmin" role="button" aria-expanded="false" aria-controls="menuAdmin">
                        <span class="d-flex align-items-center gap-2">
                            <i class="bi bi-gear-wide-connected"></i> System Settings
                        </span>
                        <i class="bi bi-chevron-down small menu-arrow"></i>
                    </a>
                    <div class="collapse" id="menuAdmin">
                        <ul class="submenu">
                            <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-people"></i> User Manager</a></li>
                            <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-sliders"></i> Risk Weights</a></li>
                            <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-cpu"></i> API Health</a></li>
                            <li><a href="#" class="nav-link disabled text-muted opacity-50"><i class="bi bi-journal-text"></i> Audit Trails</a></li>
                        </ul>
                    </div>
                </li>
            @endif
        </ul>
    </div>

    <!-- User Profile Footer Area -->
    <div class="p-3 border-top mt-auto" style="border-color: var(--color-border) !important; background-color: rgba(0, 0, 0, 0.1);">
        <div class="d-flex align-items-center gap-2 px-2 py-1">
            <div class="rounded-circle bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30 d-flex align-items-center justify-content-center fw-bold text-uppercase" style="width: 38px; height: 38px; font-size: 0.9rem;">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="overflow-hidden flex-grow-1">
                <div class="fw-semibold text-white text-truncate small" style="max-width: 140px;">{{ Auth::user()->name }}</div>
                <span class="badge bg-secondary-subtle text-secondary small py-0.5 px-2" style="font-size: 0.65rem;">{{ strtoupper(Auth::user()->role) }}</span>
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
