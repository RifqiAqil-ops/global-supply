<nav class="navbar navbar-expand-lg top-navbar-fixed px-4">
    <div class="container-fluid d-flex align-items-center justify-content-between p-0">
        <!-- Left: Logo & Mobile Toggle Button -->
        <div class="d-flex align-items-center gap-2">
            <button class="navbar-toggler d-lg-none border-0 p-1 me-1 shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileOffcanvas" aria-controls="mobileOffcanvas" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="width: 20px; height: 20px;"></span>
            </button>
            <a href="{{ url('/') }}" class="text-decoration-none d-flex align-items-center">
                <img src="{{ asset('images/logo.png') }}" alt="Waypoint Logo" class="me-2" style="height: 28px; width: auto; flex-shrink: 0; object-fit: contain;">
                <span class="text-primary fs-4 fw-bold" style="letter-spacing: 0.5px; font-family: 'Inter', sans-serif;">
                    Waypoint
                </span>
            </a>
        </div>

        <!-- Tablet-only Search Capsule -->
        <div class="d-none d-sm-block d-lg-none mx-3 flex-grow-1" style="max-width: 240px; width: 100%;">
            <form class="m-0" onsubmit="event.preventDefault();">
                <div class="position-relative search-capsule-wrapper">
                    <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                        <i class="bi bi-search small"></i>
                    </span>
                    <input type="text" class="form-control search-capsule-input" placeholder="Search countries, ports..." autocomplete="off" style="padding-right: 52px;">
                    <kbd class="search-kbd-hint" style="font-size: 0.65rem; font-family: inherit; font-weight: 600; color: #94A3B8; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 4px; padding: 2px 4px; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; transition: all 0.15s ease;">Ctrl K</kbd>
                    <!-- Autocomplete Dropdown Panel -->
                    <div class="global-search-dropdown shadow-lg p-2 position-absolute" style="display: none; top: 100%; left: 0; right: 0; z-index: 1050; max-height: 380px; overflow-y: auto; margin-top: 8px; background: #ffffff; border: 1px solid #E5E7EB; border-radius: 14px; min-width: 340px;">
                    </div>
                </div>
            </form>
        </div>

        <!-- Middle (Desktop Only): Navigation Menus & Search -->
        <div class="d-none d-lg-flex align-items-center justify-content-between flex-grow-1 ms-lg-4">
            <!-- Navigation Items -->
            <ul class="navbar-nav align-items-center gap-1">
                @php
                    $dashboardRoute = Auth::user()->isAdmin() ? 'admin.dashboard' : 'user.dashboard';
                @endphp
                
                <!-- Dashboard Link -->
                <li class="nav-item w-100 w-lg-auto">
                    <a href="{{ route($dashboardRoute) }}" class="nav-link-premium {{ Route::is($dashboardRoute) ? 'active' : '' }}">
                        Dashboard
                    </a>
                </li>

                <!-- Monitoring Dropdown -->
                <li class="nav-item dropdown w-100 w-lg-auto">
                    <a class="nav-link-premium dropdown-toggle {{ Route::is('countries.index') || Route::is('ports.index') || Route::is('weather.index') || Route::is('news.index') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Monitoring
                    </a>
                    <ul class="dropdown-menu dropdown-menu-premium">
                        <li>
                            <a class="dropdown-item {{ Route::is('countries.index') ? 'active' : '' }}" href="{{ route('countries.index') }}">
                                <i class="bi bi-flag"></i> Countries
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('ports.index') ? 'active' : '' }}" href="{{ route('ports.index') }}">
                                <i class="bi bi-compass"></i> Ports & Logistics
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('weather.index') ? 'active' : '' }}" href="{{ route('weather.index') }}">
                                <i class="bi bi-cloud-lightning"></i> Weather Alerts
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('news.index') ? 'active' : '' }}" href="{{ route('news.index') }}">
                                <i class="bi bi-newspaper"></i> Geopolitical News
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Analytics Dropdown -->
                <li class="nav-item dropdown w-100 w-lg-auto">
                    <a class="nav-link-premium dropdown-toggle {{ Route::is('compare.index') || Route::is('currency.index') || Route::is('risk-history.index') || Route::is('watchlists.index') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Analytics
                    </a>
                    <ul class="dropdown-menu dropdown-menu-premium">
                        <li>
                            <a class="dropdown-item {{ Route::is('compare.index') ? 'active' : '' }}" href="{{ route('compare.index') }}">
                                <i class="bi bi-shuffle"></i> Country Compare
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('currency.index') ? 'active' : '' }}" href="{{ route('currency.index') }}">
                                <i class="bi bi-currency-exchange"></i> Currency Monitor
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('risk-history.index') ? 'active' : '' }}" href="{{ route('risk-history.index') }}">
                                <i class="bi bi-clock-history"></i> Risk History
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('watchlists.index') ? 'active' : '' }}" href="{{ route('watchlists.index') }}">
                                <i class="bi bi-eye"></i> Watchlists
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Admin Dropdown -->
                @if (Auth::user()->isAdmin())
                <li class="nav-item dropdown w-100 w-lg-auto">
                    <a class="nav-link-premium dropdown-toggle {{ Route::is('admin.users.index') || Route::is('admin.ports.index') || Route::is('admin.articles.index') || Route::is('admin.weights.index') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-premium">
                        <li>
                            <a class="dropdown-item {{ Route::is('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="bi bi-people"></i> User Manager
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('admin.ports.index') ? 'active' : '' }}" href="{{ route('admin.ports.index') }}">
                                <i class="bi bi-compass"></i> Manage Ports
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('admin.articles.index') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}">
                                <i class="bi bi-journal-text"></i> Manage Articles
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ Route::is('admin.weights.index') ? 'active' : '' }}" href="{{ route('admin.weights.index') }}">
                                <i class="bi bi-sliders"></i> Risk Weights
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
            </ul>

            <!-- Search Capsule -->
            <div class="mx-3 mx-lg-0 flex-grow-1" style="max-width: 280px; width: 100%;">
                <form class="m-0" onsubmit="event.preventDefault();">
                    <div class="position-relative search-capsule-wrapper">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                            <i class="bi bi-search small"></i>
                        </span>
                        <input type="text" class="form-control search-capsule-input" placeholder="Search countries, ports, currency..." autocomplete="off" style="padding-right: 52px;">
                        <kbd class="search-kbd-hint d-none d-md-inline-block" style="font-size: 0.65rem; font-family: inherit; font-weight: 600; color: #94A3B8; background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 4px; padding: 2px 4px; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; transition: all 0.15s ease;">Ctrl K</kbd>
                        <!-- Autocomplete Dropdown Panel -->
                        <div class="global-search-dropdown shadow-lg p-2 position-absolute" style="display: none; top: 100%; left: 0; right: 0; z-index: 1050; max-height: 380px; overflow-y: auto; margin-top: 8px; background: #ffffff; border: 1px solid #E5E7EB; border-radius: 14px; min-width: 340px;">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side: LIVE Badge, Notification, Profile -->
        <div class="d-flex align-items-center gap-3">
            <!-- Global Live Sync Indicator -->
            <div id="liveSyncIndicator" class="live-sync-badge-modern d-flex align-items-center gap-2 px-3 py-1.5">
                <span class="live-status-dot" id="liveSyncDot"></span>
                <span class="live-status-text fw-bold text-success" id="liveSyncText">LIVE</span>
                <span class="text-muted small d-none d-sm-inline" style="font-weight: 500; font-size: 0.72rem;">Last update: <span id="liveSyncTime">--:--:--</span></span>
            </div>

            <!-- Notification Dropdown -->
            <div class="dropdown">
                <button class="btn btn-link text-muted position-relative p-2 dropdown-toggle no-caret border-0 shadow-none" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="position-absolute top-2 start-75 translate-middle p-1 bg-danger border border-light rounded-circle" style="width: 8px; height: 8px;">
                        <span class="visually-hidden">New alerts</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border border-light p-2 mt-2 dropdown-menu-modern" aria-labelledby="notifDropdown" style="min-width: 300px;">
                    <li class="px-3 py-2 d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-dark small">Recent Risk Alerts</span>
                        <a href="#" class="text-primary text-decoration-none small" style="font-size: 0.75rem;">Clear All</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-2" href="#">
                            <div class="text-danger mt-0.5"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <div>
                                <div class="text-dark small fw-semibold" style="line-height: 1.2;">Extreme Weather Alert (PH)</div>
                                <div class="text-muted small" style="font-size: 0.7rem; line-height: 1;">Typhoon approaching port of Manila.</div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item p-2 rounded d-flex gap-2" href="#">
                            <div class="text-warning mt-0.5"><i class="bi bi-graph-down"></i></div>
                            <div>
                                <div class="text-dark small fw-semibold" style="line-height: 1.2;">Currency Volatility (IDR)</div>
                                <div class="text-muted small" style="font-size: 0.7rem; line-height: 1;">IDR dropped 1.5% against USD.</div>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Vertical Divider -->
            <div style="width: 1px; height: 20px; background-color: #E5E7EB;"></div>

            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a class="dropdown-toggle text-dark d-flex align-items-center gap-2 p-1 text-decoration-none no-caret" href="#" role="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    @if (Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover; border-color: #E5E7EB !important;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold bg-primary text-white" style="width: 36px; height: 36px; font-size: 0.9rem;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                    @endif
                    <div class="text-start d-none d-md-block" style="line-height: 1.25;">
                        <div class="fw-bold text-dark small">{{ Auth::user()->name }}</div>
                        <div class="text-muted small" style="font-size: 0.72rem;">{{ ucfirst(Auth::user()->role) }}</div>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border border-light p-2 mt-2 dropdown-menu-modern" aria-labelledby="userMenuDropdown" style="min-width: 200px;">
                    <li class="px-3 py-2 small text-muted">
                        Account Email: <br>
                        <span class="text-dark fw-bold" style="font-size: 0.8rem;">{{ Auth::user()->email }}</span>
                    </li>
                    <li><hr class="dropdown-divider"></li>
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

<!-- Mobile/Tablet Offcanvas Drawer -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileOffcanvas" aria-labelledby="mobileOffcanvasLabel" style="width: 280px; border-right: 1px solid #E5E7EB;">
    <div class="offcanvas-header border-bottom px-4 py-3">
        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Waypoint Logo" style="height: 28px; width: auto;">
            <span class="text-primary fs-4 fw-bold" style="letter-spacing: 0.5px; font-family: 'Inter', sans-serif;">
                Waypoint
            </span>
        </div>
        <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div class="offcanvas-nav">
            <!-- Search capsule in Offcanvas (Visible on mobile, hidden on tablet since tablet has it in header) -->
            <div class="p-3 border-bottom d-sm-none">
                <form class="m-0" onsubmit="event.preventDefault();">
                    <div class="position-relative search-capsule-wrapper">
                        <span class="position-absolute top-50 start-0 translate-middle-y ps-3 text-muted">
                            <i class="bi bi-search small"></i>
                        </span>
                        <input type="text" class="form-control search-capsule-input" placeholder="Search countries, ports..." autocomplete="off" style="padding-right: 52px;">
                        <!-- Autocomplete Dropdown Panel -->
                        <div class="global-search-dropdown shadow-lg p-2 position-absolute" style="display: none; top: 100%; left: 0; right: 0; z-index: 1050; max-height: 380px; overflow-y: auto; margin-top: 8px; background: #ffffff; border: 1px solid #E5E7EB; border-radius: 14px; min-width: 240px;">
                        </div>
                    </div>
                </form>
            </div>

            @php
                $dashboardRoute = Auth::user()->isAdmin() ? 'admin.dashboard' : 'user.dashboard';
            @endphp

            <div class="mobile-section-title">Core</div>
            <a href="{{ route($dashboardRoute) }}" class="nav-link {{ Route::is($dashboardRoute) ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="mobile-section-title">Monitoring</div>
            <a href="{{ route('countries.index') }}" class="nav-link {{ Route::is('countries.index') ? 'active' : '' }}">
                <i class="bi bi-flag"></i> Countries
            </a>
            <a href="{{ route('ports.index') }}" class="nav-link {{ Route::is('ports.index') ? 'active' : '' }}">
                <i class="bi bi-compass"></i> Ports & Logistics
            </a>
            <a href="{{ route('weather.index') }}" class="nav-link {{ Route::is('weather.index') ? 'active' : '' }}">
                <i class="bi bi-cloud-lightning"></i> Weather Alerts
            </a>
            <a href="{{ route('news.index') }}" class="nav-link {{ Route::is('news.index') ? 'active' : '' }}">
                <i class="bi bi-newspaper"></i> Geopolitical News
            </a>

            <div class="mobile-section-title">Analytics</div>
            <a href="{{ route('compare.index') }}" class="nav-link {{ Route::is('compare.index') ? 'active' : '' }}">
                <i class="bi bi-shuffle"></i> Country Compare
            </a>
            <a href="{{ route('currency.index') }}" class="nav-link {{ Route::is('currency.index') ? 'active' : '' }}">
                <i class="bi bi-currency-exchange"></i> Currency Monitor
            </a>
            <a href="{{ route('risk-history.index') }}" class="nav-link {{ Route::is('risk-history.index') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Risk History
            </a>
            <a href="{{ route('watchlists.index') }}" class="nav-link {{ Route::is('watchlists.index') ? 'active' : '' }}">
                <i class="bi bi-eye"></i> Watchlists
            </a>

            @if (Auth::user()->isAdmin())
                <div class="mobile-section-title">Admin</div>
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ Route::is('admin.users.index') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> User Manager
                </a>
                <a href="{{ route('admin.ports.index') }}" class="nav-link {{ Route::is('admin.ports.index') ? 'active' : '' }}">
                    <i class="bi bi-compass"></i> Manage Ports
                </a>
                <a href="{{ route('admin.articles.index') }}" class="nav-link {{ Route::is('admin.articles.index') ? 'active' : '' }}">
                    <i class="bi bi-journal-text"></i> Manage Articles
                </a>
                <a href="{{ route('admin.weights.index') }}" class="nav-link {{ Route::is('admin.weights.index') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i> Risk Weights
                </a>
            @endif
        </div>
    </div>
</div>

</nav>


<style>
.global-search-dropdown {
    scrollbar-width: thin;
    scrollbar-color: #CBD5E1 #F1F5F9;
}
.global-search-dropdown::-webkit-scrollbar {
    width: 6px;
}
.global-search-dropdown::-webkit-scrollbar-track {
    background: #F1F5F9;
    border-radius: 14px;
}
.global-search-dropdown::-webkit-scrollbar-thumb {
    background-color: #CBD5E1;
    border-radius: 14px;
}
.search-group-title {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #94A3B8;
    padding: 8px 12px 4px 12px;
}
.search-item-premium {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    color: #334155;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.15s ease;
}
.search-item-premium:hover, .search-item-premium.active {
    background-color: #EFF6FF;
    color: #2563EB;
    outline: none;
}
.search-item-premium i {
    font-size: 0.95rem;
    color: #64748B;
}
.search-item-premium:hover i, .search-item-premium.active i {
    color: #2563EB;
}
.search-no-results {
    font-size: 0.8rem;
    color: #64748B;
    padding: 16px;
    text-align: center;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .4; }
}
.skeleton-pulse {
    animation: pulse 1.2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
.search-skeleton-item {
    height: 32px;
    background-color: #E2E8F0;
    border-radius: 8px;
    margin-bottom: 6px;
}

.search-action-btn {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 8px 12px;
    background-color: #EFF6FF;
    color: #2563EB;
    border: 1px solid #BFDBFE;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.12s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.search-action-btn:hover {
    background-color: #2563EB;
    color: #ffffff;
    border-color: #2563EB;
}
.search-highlight {
    color: #2563EB !important;
    font-weight: 700;
    background-color: rgba(37, 99, 235, 0.08);
    padding: 0 2px;
    border-radius: 2px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const searchInputs = document.querySelectorAll('.search-capsule-input');
    
    function safeSetItem(key, value) {
        try {
            localStorage.setItem(key, value);
        } catch (e) {
            if (e.name === 'QuotaExceededError' || e.code === 22) {
                console.warn(`Storage quota exceeded when writing key "${key}". Data will not be cached locally.`);
            } else {
                console.error(`Error saving to localStorage:`, e);
            }
        }
    }

    let countriesData = [];
    let portsData = [];
    let currenciesData = [];
    let initialized = false;

    let localResults = [];
    let dynamicResults = [];
    let currentQuery = '';
    let isFetchingDynamic = false;
    let activeIndex = -1;

    let currentAbortController = null;
    let debounceTimeout = null;

    // Levenshtein helper
    function levenshteinDistance(a, b) {
        const matrix = [];
        for (let i = 0; i <= b.length; i++) matrix[i] = [i];
        for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
        for (let i = 1; i <= b.length; i++) {
            for (let j = 1; j <= a.length; j++) {
                if (b.charAt(i - 1) === a.charAt(j - 1)) {
                    matrix[i][j] = matrix[i - 1][j - 1];
                } else {
                    matrix[i][j] = Math.min(
                        matrix[i - 1][j - 1] + 1,
                        matrix[i][j - 1] + 1,
                        matrix[i - 1][j] + 1
                    );
                }
            }
        }
        return matrix[b.length][a.length];
    }

    function cleanString(str) {
        if (!str) return '';
        return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").trim().toLowerCase();
    }

    function levenshteinFuzzyMatch(query, target) {
        const q = cleanString(query);
        const t = cleanString(target);
        if (q.length < 3) return false;
        if (t.length >= q.length) {
            const sub = t.substring(0, q.length);
            if (levenshteinDistance(q, sub) <= 1) return true;
        }
        return levenshteinDistance(q, t) <= 2;
    }

    function highlightText(text, query) {
        if (!text || !query) return text;
        const q = cleanString(query);
        const t = cleanString(text);
        const index = t.indexOf(q);
        if (index >= 0) {
            const start = index;
            const end = index + q.length;
            return text.substring(0, start) + 
                `<span class="search-highlight">` + 
                text.substring(start, end) + 
                `</span>` + 
                text.substring(end);
        }
        return text;
    }

    // Load country & port data lazily when focusing on search input
    async function lazyLoadSearchData() {
        if (initialized) return;
        initialized = true;

        renderLoadingSkeletons();

        const now = Date.now();
        const cacheExpiry = 12 * 60 * 60 * 1000; // 12 hours

        // Countries cache check
        const countriesCache = localStorage.getItem('gscrip_countries_cache');
        const countriesTime = localStorage.getItem('gscrip_countries_time');
        if (countriesCache && countriesTime && (now - parseInt(countriesTime) < cacheExpiry)) {
            countriesData = JSON.parse(countriesCache);
        } else {
            try {
                const res = await fetch('/api/countries');
                const json = await res.json();
                if (json.success) {
                    countriesData = json.data;
                    safeSetItem('gscrip_countries_cache', JSON.stringify(countriesData));
                    safeSetItem('gscrip_countries_time', now.toString());
                }
            } catch (e) {
                console.error("Error loading countries:", e);
            }
        }

        // Ports cache check
        const portsCache = localStorage.getItem('gscrip_ports_cache');
        const portsTime = localStorage.getItem('gscrip_ports_time');
        if (portsCache && portsTime && (now - parseInt(portsTime) < cacheExpiry)) {
            portsData = JSON.parse(portsCache);
        } else {
            try {
                const res = await fetch('/api/ports');
                const json = await res.json();
                if (json.success) {
                    portsData = json.data;
                    safeSetItem('gscrip_ports_cache', JSON.stringify(portsData));
                    safeSetItem('gscrip_ports_time', now.toString());
                }
            } catch (e) {
                console.error("Error loading ports:", e);
            }
        }

        // Currencies cache check
        const currencyCache = localStorage.getItem('gscrip_currency_cache');
        const currencyTime = localStorage.getItem('gscrip_currency_time');
        if (currencyCache && currencyTime && (now - parseInt(currencyTime) < cacheExpiry)) {
            currenciesData = JSON.parse(currencyCache);
        } else {
            try {
                const res = await fetch('/api/currency');
                const json = await res.json();
                if (json.success) {
                    currenciesData = json.data;
                    safeSetItem('gscrip_currency_cache', JSON.stringify(currenciesData));
                    safeSetItem('gscrip_currency_time', now.toString());
                }
            } catch (e) {
                console.error("Error loading currencies:", e);
            }
        }

        // Clean skeletons and render results
        const activeInput = document.activeElement;
        if (activeInput && activeInput.classList.contains('search-capsule-input')) {
            const val = activeInput.value.trim();
            const wrapper = activeInput.closest('.search-capsule-wrapper');
            const dropdown = wrapper.querySelector('.global-search-dropdown');
            if (val) {
                handleSearchInput(activeInput, dropdown);
            } else {
                renderHistoryAndPopular(dropdown);
            }
        }
    }

    function renderLoadingSkeletons() {
        document.querySelectorAll('.global-search-dropdown').forEach(dropdown => {
            dropdown.innerHTML = `
                <div class="p-3">
                    <div class="d-flex align-items-center gap-2 mb-3 text-muted small fw-bold text-uppercase">
                        <div class="spinner-border spinner-border-sm text-primary" role="status" style="width: 12.5px; height: 12.5px; border-width: 1.5px;"></div>
                        <span>Initializing Search Directory...</span>
                    </div>
                    <div class="skeleton-pulse">
                        <div class="search-skeleton-item" style="width: 90%;"></div>
                        <div class="search-skeleton-item" style="width: 75%;"></div>
                        <div class="search-skeleton-item" style="width: 80%;"></div>
                    </div>
                </div>
            `;
            dropdown.style.display = 'block';
        });
    }

    function handleSearchInput(input, dropdown) {
        const query = input.value.trim();

        if (!query) {
            activeIndex = -1;
            dropdown.style.display = 'none';
            return;
        }

        if (currentAbortController) {
            currentAbortController.abort();
        }
        currentAbortController = new AbortController();

        renderLocalResults(query, dropdown);

        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
            fetchDynamicResults(query, dropdown, currentAbortController.signal);
        }, 300);
    }

    function renderLocalResults(query, dropdown) {
        const q = cleanString(query);
        localResults = [];

        // 1. Countries Match
        let matchedCountries = [];
        countriesData.forEach(c => {
            const name = c.name;
            const nameClean = cleanString(name);
            const iso2 = c.iso2 ? c.iso2.toLowerCase() : '';
            const iso3 = c.iso3 ? c.iso3.toLowerCase() : '';
            const capital = c.capital ? c.capital.toLowerCase() : '';

            let score = 0;
            if (iso2 === q || iso3 === q) score = 1000;
            else if (nameClean === q) score = 950;
            else if (nameClean.startsWith(q)) score = 900;
            else if (nameClean.includes(q)) score = 800;
            else if (capital.includes(q)) score = 700;

            if (score > 0) {
                matchedCountries.push({ item: c, score });
            }
        });

        // 2. Ports Match
        let matchedPorts = [];
        portsData.forEach(p => {
            const name = p.name;
            const nameClean = cleanString(name);
            const portCode = p.port_code ? p.port_code.toLowerCase() : '';
            const unLocode = p.un_locode ? p.un_locode.toLowerCase() : '';

            let score = 0;
            if (portCode === q || unLocode === q) score = 950;
            else if (nameClean === q) score = 900;
            else if (nameClean.startsWith(q)) score = 800;
            else if (nameClean.includes(q)) score = 700;

            if (score > 0) {
                matchedPorts.push({ item: p, score });
            }
        });

        // 3. Currency Match
        let matchedCurrencies = [];
        currenciesData.forEach(rate => {
            const code = rate.currency_code ? rate.currency_code.toLowerCase() : '';
            const name = rate.currency_name ? rate.currency_name.toLowerCase() : '';
            const countryName = rate.country ? rate.country.name.toLowerCase() : '';

            let score = 0;
            if (code === q) score = 950;
            else if (name === q) score = 900;
            else if (code.startsWith(q)) score = 850;
            else if (name.includes(q) || countryName.includes(q)) score = 750;

            if (score > 0) {
                if (!matchedCurrencies.some(mc => mc.item.currency_code === rate.currency_code)) {
                    matchedCurrencies.push({ item: rate, score });
                }
            }
        });

        // 4. Fuzzy Levenshtein Match fallback
        if (matchedCountries.length === 0 && matchedPorts.length === 0 && matchedCurrencies.length === 0) {
            countriesData.forEach(c => {
                const name = c.name;
                if (levenshteinFuzzyMatch(q, name)) {
                    matchedCountries.push({ item: c, score: 500 });
                }
            });
            portsData.forEach(p => {
                const name = p.name;
                if (levenshteinFuzzyMatch(q, name)) {
                    matchedPorts.push({ item: p, score: 500 });
                }
            });
        }

        // Map Countries
        matchedCountries.forEach(match => {
            const c = match.item;
            const flagStyle = `width: 18px; height: 12px; border-radius: 2px; object-fit: cover;`;
            localResults.push({
                category: 'Countries',
                iconHtml: c.flag_url ? `<img src="${c.flag_url}" style="${flagStyle}" class="border shadow-sm">` : '🏳️',
                title: c.name,
                subtitle: `ISO: ${c.iso3}${c.capital ? ' | Capital: ' + c.capital : ''}`,
                highlightedTitle: highlightText(c.name, query),
                highlightedSubtitle: highlightText(`ISO: ${c.iso3}${c.capital ? ' | Capital: ' + c.capital : ''}`, query),
                url: `/countries/${c.iso2}`,
                score: match.score
            });

            // Generate Weather suggestion
            let weatherScore = match.score - 100;
            if (weatherScore > 0) {
                localResults.push({
                    category: 'Weather',
                    icon: 'bi-cloud-lightning-rain',
                    title: `${c.name} Weather`,
                    subtitle: `View real-time weather & extreme alerts for ${c.name}`,
                    highlightedTitle: highlightText(`${c.name} Weather`, query),
                    highlightedSubtitle: highlightText(`View real-time weather & extreme alerts for ${c.name}`, query),
                    url: `/weather?search=${encodeURIComponent(c.name)}`,
                    score: weatherScore
                });
            }
        });

        // Map Ports
        matchedPorts.forEach(match => {
            const p = match.item;
            const countryName = p.country ? p.country.name : '';
            const countryCode = p.country ? p.country.iso2 : '';
            localResults.push({
                category: 'Ports',
                icon: 'bi-anchor',
                title: p.name,
                subtitle: `UN LOCODE: ${p.un_locode || 'N/A'} | ${countryName || countryCode}`,
                highlightedTitle: highlightText(p.name, query),
                highlightedSubtitle: highlightText(`UN LOCODE: ${p.un_locode || 'N/A'} | ${countryName || countryCode}`, query),
                url: `/ports?search=${encodeURIComponent(p.name)}`,
                score: match.score
            });
        });

        // Map Currencies
        matchedCurrencies.forEach(match => {
            const rate = match.item;
            const code = rate.currency_code;
            const name = rate.currency_name || 'Currency';
            localResults.push({
                category: 'Currency',
                icon: 'bi-currency-exchange',
                title: `${code} - ${name}`,
                subtitle: `Exchange Rate: ${rate.exchange_rate} USD | ${rate.country ? rate.country.name : ''}`,
                highlightedTitle: highlightText(`${code} - ${name}`, query),
                highlightedSubtitle: highlightText(`Exchange Rate: ${rate.exchange_rate} USD | ${rate.country ? rate.country.name : ''}`, query),
                url: `/currency`,
                score: match.score
            });
        });

        isFetchingDynamic = true;
        renderMergedList(dropdown);
    }

    async function fetchDynamicResults(query, dropdown, signal) {
        const queryEscaped = encodeURIComponent(query);
        try {
            const [newsJson, articlesHtml, watchlistHtml] = await Promise.all([
                fetch('/api/news', { signal }).then(res => res.json()).catch(() => ({ success: false })),
                fetch(`/articles?search=${queryEscaped}`, { signal }).then(res => res.text()).catch(() => ''),
                fetch(`/watchlists?search=${queryEscaped}`, { signal }).then(res => res.text()).catch(() => '')
            ]);

            if (signal.aborted) return;
            isFetchingDynamic = false;

            const parsedDynamicResults = [];

            // News parsing
            if (newsJson && newsJson.success && Array.isArray(newsJson.data)) {
                newsJson.data.forEach(item => {
                    const title = item.title || '';
                    const desc = item.description || '';
                    const countryName = item.country ? item.country.name : '';
                    
                    const titleClean = cleanString(title);
                    const descClean = cleanString(desc);
                    const countryClean = cleanString(countryName);
                    const q = cleanString(query);
                    
                    let score = 0;
                    if (titleClean.startsWith(q)) score = 750;
                    else if (titleClean.includes(q)) score = 650;
                    else if (descClean.includes(q)) score = 500;
                    else if (countryClean.includes(q)) score = 450;
                    
                    if (score > 0) {
                        parsedDynamicResults.push({
                            category: 'News',
                            icon: 'bi-newspaper',
                            title: title,
                            subtitle: `${countryName ? countryName + ' | ' : ''}${desc.substring(0, 60)}...`,
                            highlightedTitle: highlightText(title, query),
                            highlightedSubtitle: highlightText(`${countryName ? countryName + ' | ' : ''}${desc.substring(0, 60)}...`, query),
                            url: `/news?search=${encodeURIComponent(title)}`,
                            score: score
                        });
                    }
                });
            }

            // Articles parsing
            if (articlesHtml) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(articlesHtml, 'text/html');
                const cards = doc.querySelectorAll('.card');
                cards.forEach(card => {
                    const a = card.querySelector('a[href*="/articles/"]');
                    if (a) {
                        const title = a.textContent.trim();
                        const url = a.getAttribute('href');
                        const summaryEl = card.querySelector('p.text-muted');
                        const summary = summaryEl ? summaryEl.textContent.trim() : '';
                        
                        const titleClean = cleanString(title);
                        const sumClean = cleanString(summary);
                        const q = cleanString(query);
                        
                        let score = 0;
                        if (titleClean.startsWith(q)) score = 700;
                        else if (titleClean.includes(q)) score = 600;
                        else if (sumClean.includes(q)) score = 500;
                        
                        if (score > 0) {
                            parsedDynamicResults.push({
                                category: 'Articles',
                                icon: 'bi-journal-text',
                                title: title,
                                subtitle: summary.substring(0, 60) + '...',
                                highlightedTitle: highlightText(title, query),
                                highlightedSubtitle: highlightText(summary.substring(0, 60) + '...', query),
                                url: url,
                                score: score
                            });
                        }
                    }
                });
            }

            // Watchlists parsing
            if (watchlistHtml) {
                const parser = new DOMParser();
                const doc = parser.parseFromString(watchlistHtml, 'text/html');
                const rows = doc.querySelectorAll('table tbody tr');
                rows.forEach(row => {
                    const a = row.querySelector('td a[href*="/countries/"]');
                    if (a) {
                        const strong = a.querySelector('strong');
                        if (strong) {
                            const countryName = strong.textContent.trim();
                            const noteTd = row.querySelector('td:nth-child(5)');
                            const notes = noteTd ? noteTd.textContent.trim() : '';
                            
                            const countryClean = cleanString(countryName);
                            const notesClean = cleanString(notes);
                            const q = cleanString(query);
                            
                            let score = 0;
                            if (countryClean === q) score = 800;
                            else if (countryClean.includes(q)) score = 700;
                            else if (notesClean.includes(q)) score = 500;
                            
                            if (score > 0) {
                                parsedDynamicResults.push({
                                    category: 'Watchlists',
                                    icon: 'bi-star-fill',
                                    title: `Primary Watchlist - ${countryName}`,
                                    subtitle: notes !== '—' ? notes : 'No custom notes',
                                    highlightedTitle: highlightText(`Primary Watchlist - ${countryName}`, query),
                                    highlightedSubtitle: highlightText(notes !== '—' ? notes : 'No custom notes', query),
                                    url: `/watchlists?search=${encodeURIComponent(countryName)}`,
                                    score: score
                                });
                            }
                        }
                    }
                });
            }

            dynamicResults = parsedDynamicResults;
            renderMergedList(dropdown);
        } catch (e) {
            if (e.name !== 'AbortError') {
                console.error("Error fetching dynamic results:", e);
                isFetchingDynamic = false;
                renderMergedList(dropdown);
            }
        }
    }

    function renderMergedList(dropdown) {
        const allItems = [...localResults, ...dynamicResults];
        
        if (allItems.length === 0 && !isFetchingDynamic) {
            renderEmptyState(dropdown);
            return;
        }

        const categories = {
            Countries: { title: "🌍 Countries", items: [], maxScore: 0 },
            Ports: { title: "⚓ Ports", items: [], maxScore: 0 },
            Currency: { title: "💱 Currency", items: [], maxScore: 0 },
            Weather: { title: "🌦 Weather", items: [], maxScore: 0 },
            News: { title: "📰 News", items: [], maxScore: 0 },
            Articles: { title: "📝 Articles", items: [], maxScore: 0 },
            Watchlists: { title: "⭐ Watchlists", items: [], maxScore: 0 }
        };

        allItems.forEach(item => {
            categories[item.category].items.push(item);
            categories[item.category].maxScore = Math.max(categories[item.category].maxScore, item.score);
        });

        const sortedKeys = Object.keys(categories).filter(key => {
            return categories[key].items.length > 0 || (isFetchingDynamic && ['News', 'Articles', 'Watchlists'].includes(key));
        }).sort((a, b) => {
            const scoreA = categories[a].items.length > 0 ? categories[a].maxScore : 100;
            const scoreB = categories[b].items.length > 0 ? categories[b].maxScore : 100;
            return scoreB - scoreA;
        });

        let html = '';
        sortedKeys.forEach(key => {
            html += `<div class="search-group-title">${categories[key].title}</div>`;
            if (categories[key].items.length === 0) {
                html += `
                    <div class="skeleton-pulse px-3 py-1">
                        <div class="search-skeleton-item" style="width: 85%; height: 24px; margin-bottom: 4px;"></div>
                    </div>
                `;
            } else {
                categories[key].items.sort((a, b) => b.score - a.score);
                categories[key].items.slice(0, 5).forEach(item => {
                    html += `
                        <a href="${item.url}" class="search-item-premium" data-url="${item.url}">
                            ${item.iconHtml ? item.iconHtml : `<i class="bi ${item.icon}"></i>`}
                            <div class="d-flex flex-column" style="line-height: 1.35; margin-left: 2px;">
                                <span class="fw-semibold text-dark search-item-title">${item.highlightedTitle || item.title}</span>
                                ${item.subtitle ? `<span class="text-muted small" style="font-size: 0.72rem;">${item.highlightedSubtitle || item.subtitle}</span>` : ''}
                            </div>
                        </a>
                    `;
                });
            }
        });

        dropdown.innerHTML = html;
        dropdown.style.display = 'block';

        updateKeyboardHighlight(dropdown);
    }

    function renderEmptyState(dropdown) {
        dropdown.innerHTML = `
            <div class="search-no-results py-4">
                <div class="text-muted mb-3" style="font-size: 0.85rem;"><i class="bi bi-search me-1"></i> No matching result found.</div>
                <div class="d-flex justify-content-center gap-2">
                    <a href="/countries" class="search-action-btn">
                        <i class="bi bi-flag"></i> Search Countries
                    </a>
                    <a href="/ports" class="search-action-btn">
                        <i class="bi bi-anchor"></i> Search Ports
                    </a>
                </div>
            </div>
        `;
        dropdown.style.display = 'block';
    }

    function updateKeyboardHighlight(dropdown) {
        const items = dropdown.querySelectorAll('.search-item-premium');
        items.forEach((it, idx) => {
            if (idx === activeIndex) {
                it.classList.add('active');
                it.scrollIntoView({ block: 'nearest' });
            } else {
                it.classList.remove('active');
            }
        });
    }

    function handleSearchSubmit(val, dropdown) {
        const query = val.trim();
        if (!query) return;

        const allItems = [...localResults, ...dynamicResults];
        
        if (allItems.length === 1) {
            window.location.href = allItems[0].url;
            return;
        }

        const items = dropdown.querySelectorAll('.search-item-premium');
        if (activeIndex >= 0 && items[activeIndex]) {
            window.location.href = items[activeIndex].getAttribute('href');
            return;
        }

        if (allItems.length > 0) {
            allItems.sort((a, b) => b.score - a.score);
            window.location.href = allItems[0].url;
        } else {
            window.location.href = `/ports?search=${encodeURIComponent(query)}`;
        }
    }

    // Attach listeners
    searchInputs.forEach(input => {
        const wrapper = input.closest('.search-capsule-wrapper');
        const dropdown = wrapper.querySelector('.global-search-dropdown');

        input.addEventListener('focus', () => {
            lazyLoadSearchData();
            activeIndex = -1;
            if (input.value.trim().length > 0) {
                handleSearchInput(input, dropdown);
            } else {
                dropdown.style.display = 'none';
            }
        });

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.style.display = 'none';
                activeIndex = -1;
            }
        });

        input.addEventListener('input', () => {
            activeIndex = -1;
            handleSearchInput(input, dropdown);
        });

        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('.search-item-premium');
            
            if (e.key === 'ArrowDown' || (e.key === 'Tab' && !e.shiftKey)) {
                e.preventDefault();
                if (dropdown.style.display === 'none') {
                    handleSearchInput(input, dropdown);
                    return;
                }
                if (items.length > 0) {
                    activeIndex = (activeIndex + 1) % items.length;
                    updateKeyboardHighlight(dropdown);
                }
            } else if (e.key === 'ArrowUp' || (e.key === 'Tab' && e.shiftKey)) {
                e.preventDefault();
                if (items.length > 0) {
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateKeyboardHighlight(dropdown);
                }
            } else if (e.key === 'Escape') {
                dropdown.style.display = 'none';
                activeIndex = -1;
                input.blur();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                handleSearchSubmit(input.value, dropdown);
            }
        });

        dropdown.addEventListener('mousemove', (e) => {
            const item = e.target.closest('.search-item-premium');
            if (item) {
                const items = Array.from(dropdown.querySelectorAll('.search-item-premium'));
                activeIndex = items.indexOf(item);
                items.forEach(it => it.classList.remove('active'));
                item.classList.add('active');
            }
        });

        dropdown.addEventListener('click', (e) => {
            const item = e.target.closest('.search-item-premium');
            if (item) {
                return;
            }
        });
    });

    // Global shortcut
    document.addEventListener('keydown', (e) => {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        const triggerKey = isMac ? e.metaKey : e.ctrlKey;
        if (triggerKey && e.key === 'k') {
            e.preventDefault();
            const activeInput = document.querySelector('.search-capsule-input');
            if (activeInput) {
                activeInput.focus();
                activeInput.select();
            }
        }
    });

    // Update kbd text on Mac
    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    document.querySelectorAll('.search-kbd-hint').forEach(el => {
        el.textContent = isMac ? '⌘K' : 'Ctrl K';
    });
});
</script>
