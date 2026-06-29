<nav class="navbar navbar-expand-lg border-bottom py-2.5" style="background-color: var(--color-bg-sidebar); border-color: var(--color-border) !important;">
    <div class="container-fluid px-4">
        <!-- Sidebar Toggle Button for Mobile/Tablet -->
        <button class="btn btn-link text-muted d-lg-none me-3 p-0 border-0" id="sidebarToggleBtn" type="button">
            <i class="bi bi-list fs-3"></i>
        </button>

        <!-- Search Bar Mockup/Placeholder -->
        <div class="d-none d-md-block me-auto" style="min-width: 300px;">
            <form class="m-0" onsubmit="event.preventDefault();">
                <div class="position-relative">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                        <i class="bi bi-search small"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm ps-5 bg-dark bg-opacity-20 border-secondary border-opacity-30 rounded-pill text-white" placeholder="Search countries, ports, codes..." style="font-size: 0.85rem;">
                </div>
            </form>
        </div>

        <!-- Navbar Controls & User Profiles -->
        <div class="d-flex align-items-center gap-3">
            <!-- Notification Dropdown Mockup -->
            <div class="dropdown">
                <button class="btn btn-link text-muted position-relative p-2 dropdown-toggle no-caret border-0 shadow-none" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-1 start-75 translate-middle p-1 bg-danger border border-light rounded-circle" style="border-color: var(--color-bg-sidebar) !important;">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border border-secondary dropdown-menu-dark p-2 mt-2" aria-labelledby="notifDropdown" style="background-color: var(--color-bg-sidebar); min-width: 280px;">
                    <li class="px-3 py-2 d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold text-white small">Recent Risk Alerts</span>
                        <a href="#" class="text-primary text-decoration-none small" style="font-size: 0.75rem;">Clear All</a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <!-- Mockup item 1 -->
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-2" href="#">
                            <div class="text-danger mt-0.5"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <div>
                                <div class="text-white small fw-medium" style="line-height: 1.2;">Extreme Weather Alert (PH)</div>
                                <div class="text-muted small" style="font-size: 0.7rem; line-height: 1;">Typhoon approaching port of Manila.</div>
                            </div>
                        </a>
                    </li>
                    <!-- Mockup item 2 -->
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-2" href="#">
                            <div class="text-warning mt-0.5"><i class="bi bi-graph-down"></i></div>
                            <div>
                                <div class="text-white small fw-medium" style="line-height: 1.2;">Currency Volatility (IDR)</div>
                                <div class="text-muted small" style="font-size: 0.7rem; line-height: 1;">IDR dropped 1.5% against USD.</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Vertical Divider -->
            <div style="width: 1px; height: 20px; background-color: var(--color-border);"></div>

            <!-- User Menu Profile Dropdown -->
            <div class="dropdown">
                <a class="dropdown-toggle text-white d-flex align-items-center gap-2 p-1 text-decoration-none no-caret" href="#" role="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    @if (Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover; border-color: var(--color-border) !important;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold bg-primary text-white" style="width: 32px; height: 32px; font-size: 0.85rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="text-start d-none d-md-block">
                        <div class="fw-semibold text-white small" style="line-height: 1.2;">{{ Auth::user()->name }}</div>
                        <div class="text-muted small" style="font-size: 0.75rem; line-height: 1;">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border border-secondary dropdown-menu-dark p-2 mt-2" aria-labelledby="userMenuDropdown" style="background-color: var(--color-bg-sidebar);">
                    <li class="px-3 py-2 small text-muted">
                        Account Email: <br>
                        <span class="text-white fw-medium">{{ Auth::user()->email }}</span>
                    </li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 rounded" href="#">
                            <i class="bi bi-person"></i> My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 rounded" href="#">
                            <i class="bi bi-gear"></i> Preferences
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item d-flex align-items-center gap-2 rounded text-danger w-100 border-0 bg-transparent text-start shadow-none">
                                <i class="bi bi-box-arrow-right"></i> Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
