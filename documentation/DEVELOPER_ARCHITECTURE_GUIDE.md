# WAYPOINT (Global Supply Chain Intelligence)
## Comprehensive Developer Architecture & Maintenance Manual

> **Target Audience**: Future Maintainers & Lead Developers  
> **Purpose**: Complete guide to understanding, debugging, extending, and maintaining the Waypoint Laravel application.

---

# TABLE OF CONTENTS
1. [How the Entire Application Works (Lifecycle Overview)](#how-the-entire-application-works)
2. [Feature 1: User Registration](#feature-1-user-registration)
3. [Feature 2: User Authentication & Login](#feature-2-user-authentication--login)
4. [Feature 3: Main Overview Dashboard](#feature-3-main-overview-dashboard)
5. [Feature 4: Global Countries Module](#feature-4-global-countries-module)
6. [Feature 5: Country Profile & Live Intelligence Detail](#feature-5-country-profile--live-intelligence-detail)
7. [Feature 6: Open-Meteo Weather Integration](#feature-6-open-meteo-weather-integration)
8. [Feature 7: Currency Impact & Valas Dashboard](#feature-7-currency-impact--valas-dashboard)
9. [Feature 8: Risk Scoring Engine & Intelligence Dashboard](#feature-8-risk-scoring-engine--intelligence-dashboard)
10. [Feature 9: Telemetry & Observability Monitoring Dashboard](#feature-9-telemetry--observability-monitoring-dashboard)
11. [Feature 10: GNews Intelligence Feed](#feature-10-gnews-intelligence-feed)
12. [Feature 11: Watchlist & Favorites Module](#feature-11-watchlist--favorites-module)
13. [Feature 12: Country Comparison Tool](#feature-12-country-comparison-tool)
14. [Feature 13: Global Shipment Route Calculator](#feature-13-global-shipment-route-calculator)
15. [Feature 14: User Profile & Preferences](#feature-14-user-profile--preferences)
16. [Feature 15: Admin Operations & Sync Manager Panel](#feature-15-admin-operations--sync-manager-panel)
17. [Feature 16: Railway Containerized Production Deployment](#feature-16-railway-containerized-production-deployment)

---

# HOW THE ENTIRE APPLICATION WORKS

Waypoint operates on an **Asynchronous Data Architecture (Zero HTTP Wait)**. 

### The Core Architectural Rule:
> **The user's HTTP request must NEVER wait for external APIs.**  
> When a user opens any page, data is read directly from the local database / cache in **< 10ms**.  
> Heavy API synchronizations run asynchronously via background Queue Jobs or the Laravel Scheduler.

### Complete Lifecycle Diagram:
```
Browser Request
     ↓
1. HTTP Request (e.g. GET /user/dashboard)
     ↓
2. Laravel Router (routes/web.php)
     ↓
3. Global Middleware Stack (bootstrap/app.php)
   ├─ SecurityHeadersMiddleware (Attaches X-Frame, CSP, XSS headers)
   └─ AutoBootstrapMiddleware (Verifies if database is populated; runs background setup if needed)
     ↓
4. Controller Execution (e.g. UserDashboardController@index)
     ↓
5. Repository / Service Query Layer (e.g. CountryRepository, RiskScoringEngine)
     ↓
6. Cache Layer Check (Cache::remember / Cache::put)
   ├─ HIT  => Return cached data (< 1ms)
   └─ MISS => Query local MySQL / SQLite database (< 5ms)
     ↓
7. Controller Aggregation
     ↓
8. Blade View Rendering (resources/views/user/user_dashboard.blade.php)
   └─ Sub-components: <x-stat-card>, <x-card>, <x-table>, Chart.js
     ↓
9. HTTP 200 Response sent back to Browser (Total time < 10ms)
```

---

# FEATURE 1: USER REGISTRATION

### 1. Purpose
Allows new logistics analysts or company administrators to create an account on Waypoint to access global supply chain analytics.

### 2. User Flow
```
User navigates to /register
↓
Route: GET /register
↓
Middleware: guest
↓
Controller: RegisterController@showRegistrationForm
↓
Blade View: resources/views/auth/register.blade.php
↓
User fills Name, Email, Password & clicks "Create Account"
↓
Route: POST /register
↓
Controller: RegisterController@register (Validates input, hashes password)
↓
Database: User::create() into `users` table
↓
Auth::login($user)
↓
Redirect: /user/dashboard (HTTP 302 -> 200)
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\Auth\RegisterController`
- **Model**: `App\Models\User`
- **Database Table**: `users`
- **Blade View**: `resources/views/auth/register.blade.php`

### 4. Data Journey
```
User Form Input -> RegisterController (Validation & Hash::make) -> MySQL users Table -> Auth Session -> User Dashboard View
```

### 5. Files Involved
- **Route**: `routes/web.php` (Line 38)
- **Controller**: `app/Http/Controllers/Auth/RegisterController.php`
- **Model**: `app/Models/User.php`
- **Migration**: `database/migrations/0001_01_01_000000_create_users_table.php`
- **Blade**: `resources/views/auth/register.blade.php`

### 6. If Something Breaks
- **Common Cause**: Email already taken or password validation mismatch.
- **Where to Check**: `storage/logs/laravel.log`. Look for `ValidationException` or SQL duplicate entry errors.

### 7. If I Want to Modify This Feature
- **Files to Edit**: `RegisterController.php` (for validation rules), `register.blade.php` (for UI form).
- **Files NOT to Edit**: `0001_01_01_000000_create_users_table.php` (already migrated in production).

---

# FEATURE 2: USER AUTHENTICATION & LOGIN

### 1. Purpose
Authenticates registered users, issues secure HTTP session cookies, and routes users according to their role (`admin` or `user`).

### 2. User Flow
```
User navigates to /login
↓
Route: GET /login
↓
Middleware: guest
↓
Controller: LoginController@showLoginForm
↓
Blade View: resources/views/auth/login.blade.php
↓
User inputs email & password -> Submits
↓
Route: POST /login
↓
Controller: LoginController@login -> Auth::attempt()
↓
Role Evaluation:
  ├─ Admin => Redirect to /admin/dashboard
  └─ User  => Redirect to /user/dashboard
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\Auth\LoginController`
- **Model**: `App\Models\User`
- **Database Table**: `users`, `sessions`

### 4. Data Journey
```
Login Form -> LoginController -> Bcrypt Hash Check against `users.password` -> Session Cookie Created -> Dashboard
```

### 5. Files Involved
- **Route**: `routes/web.php` (Lines 34-35)
- **Controller**: `app/Http/Controllers/Auth/LoginController.php`
- **Model**: `app/Models/User.php`
- **Blade**: `resources/views/auth/login.blade.php`

### 6. If Something Breaks
- **Common Cause**: Invalid credentials or session driver misconfiguration.
- **Logs**: `storage/logs/laravel.log`. Check if `SESSION_DRIVER` is set to `database` and `sessions` table exists.

---

# FEATURE 3: MAIN OVERVIEW DASHBOARD

### 1. Purpose
Serves as the primary command center for supply chain managers, summarizing global risk scores, interactive Leaflet world map, key indicators, and real-time news feeds.

### 2. User Flow
```
User clicks "Dashboard"
↓
Route: GET /user/dashboard
↓
Middleware: auth, SecurityHeadersMiddleware, AutoBootstrapMiddleware
↓
Controller: UserDashboardController@index
↓
Repository: CountryRepository, RiskScoringEngine
↓
Database: Queries `countries`, `country_risk_scores`, `news_articles`
↓
Blade View: resources/views/user/user_dashboard.blade.php
↓
Browser renders Leaflet Map & Chart.js widgets
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\User\DashboardController`
- **Services/Repositories**: `CountryRepositoryInterface`, `RiskScoringEngineInterface`
- **Blade View**: `resources/views/user/user_dashboard.blade.php`

### 4. Data Journey
```
Database (countries + risk_scores + news) -> UserDashboardController -> Blade View -> Leaflet JS / Chart.js Rendering
```

### 5. Files Involved
- **Route**: `routes/web.php` (Line 65)
- **Controller**: `app/Http/Controllers/User/DashboardController.php`
- **Repository**: `app/Repositories/CountryRepository.php`
- **Blade**: `resources/views/user/user_dashboard.blade.php`

---

# FEATURE 4: GLOBAL COUNTRIES MODULE

### 1. Purpose
Provides a searchable, filterable, and paginated directory of all 195 world countries, displaying their GDP, population, inflation, risk index, and operational weather status.

### 2. User Flow
```
User clicks "Countries"
↓
Route: GET /countries
↓
Controller: CountryController@index
↓
Query Filters: Search string, region filter, GDP range, Inflation range, sorting
↓
Repository: CountryRepository@paginateFiltered(15)
↓
Database: Select with Eager Loading (`with(['latestWeather', 'economicIndicators', 'latestRiskScore'])`)
↓
Blade View: resources/views/user/countries_index.blade.php
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\CountryController`
- **Repository**: `App\Repositories\CountryRepository`
- **Model**: `App\Models\Country`

### 4. Data Journey
```
HTTP Query Params (?search=Indonesia&region=Asia) -> CountryRepository -> MySQL `countries` table -> Paginated Blade View
```

### 5. Files Involved
- **Route**: `routes/web.php` (Line 103)
- **Controller**: `app/Http/Controllers/CountryController.php`
- **Repository**: `app/Repositories/CountryRepository.php`
- **Blade**: `resources/views/user/countries_index.blade.php`

---

# FEATURE 5: COUNTRY PROFILE & LIVE INTELLIGENCE DETAIL

### 1. Purpose
Displays an in-depth 360-degree risk profile for a single country (e.g. Indonesia / ISO: ID), including macroeconomics, UN/LOCODE cargo ports, Open-Meteo local weather, exchange rates, and GNews geopolitical headlines.

### 2. User Flow
```
User clicks a Country (e.g. /countries/ID)
↓
Route: GET /countries/{code}
↓
Controller: CountryController@show('ID')
↓
Services Triggered (Reads DB first, falls back to external API if unseeded):
  ├─ WorldBankService (GDP, Inflation, Population)
  ├─ RestCountriesService (Flag, Capital, Subregion)
  ├─ OpenMeteoService (Temperature, Wind, Extremes)
  ├─ ExchangeRateService (USD & IDR conversion)
  └─ GNewsService (Geopolitical & Trade News)
↓
Risk Engine: RiskScoringEngine@calculateCountryScore(country_id)
↓
Blade View: resources/views/user/country_detail.blade.php
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\CountryController@show`
- **Models**: `Country`, `Port`, `WeatherDatum`, `ExchangeRate`, `EconomicIndicator`, `NewsArticle`, `CountryRiskScore`
- **Services**: All 5 external service clients + `RiskScoringEngine`

### 4. Data Journey
```
Local Database Tables (Fallback to API Clients if missing) -> CountryController -> RiskScoringEngine -> Blade View (`country_detail.blade.php`)
```

### 5. Files Involved
- **Route**: `routes/web.php` (Line 105)
- **Controller**: `app/Http/Controllers/CountryController.php`
- **Services**: `app/Services/External/*`, `app/Services/Internal/RiskScoringEngine.php`
- **Blade**: `resources/views/user/country_detail.blade.php`

---

# FEATURE 6: OPEN-METEO WEATHER INTEGRATION

### 1. Purpose
Monitors real-time weather conditions and extreme weather alerts (typhoons, high winds, freezing temperatures) for cargo ports and logistics routes across 195 countries.

### 2. User Flow
```
Laravel Scheduler / Command / Country Detail Request
↓
Service: OpenMeteoService@syncAllWeather()
↓
Batch Coordinates Processing (50 locations per HTTP request)
↓
API: GET https://api.open-meteo.com/v1/forecast?latitude=X&longitude=Y...
↓
Database: `weather_data` table (updateOrCreate)
↓
Weather Risk Calculation: RiskScoringEngine
```

### 3. Internal Logic
- **Service**: `App\Services\External\OpenMeteoService`
- **Model**: `App\Models\WeatherDatum`
- **Database Table**: `weather_data`

### 4. Files Involved
- **Service**: `app/Services/External/OpenMeteoService.php`
- **Command**: `app/Console/Commands/SyncWeatherCommand.php`
- **Model**: `app/Models/WeatherDatum.php`

---

# FEATURE 7: CURRENCY IMPACT & VALAS DASHBOARD

### 1. Purpose
Tracks 139+ foreign currencies against USD & IDR, offering 15-day daily rate snapshots, weekly & monthly percentage movement calculations, top gainers, and top losers.

### 2. User Flow
```
User clicks "Currency"
↓
Route: GET /currency
↓
Controller: CurrencyController@index
↓
Service Helper: ExchangeRateService@ensureHistoricalSnapshots() (Seeds 15-day snapshots if missing)
↓
Calculations:
  ├─ Daily % Change
  ├─ Weekly (7-day) % Change
  ├─ Monthly (30-day) % Change
  ├─ Top 5 Gainers & Top 5 Losers
  └─ 14-day Chart.js Trendlines (EUR, GBP, JPY, CNY, IDR)
↓
Blade View: resources/views/user/currency.blade.php
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\User\CurrencyController`
- **Service**: `App\Services\External\ExchangeRateService`
- **Model**: `App\Models\ExchangeRate`
- **Database Table**: `exchange_rates`

### 4. Files Involved
- **Route**: `routes/web.php` (Line 111)
- **Controller**: `app/Http/Controllers/User/CurrencyController.php`
- **Service**: `app/Services/External/ExchangeRateService.php`
- **Blade**: `resources/views/user/currency.blade.php`

---

# FEATURE 8: RISK SCORING ENGINE & INTELLIGENCE DASHBOARD

### 1. Purpose
Calculates a weighted composite risk index (0–100) for every country by evaluating 5 risk dimensions: Economic (20%), Weather (20%), Currency (20%), Geopolitical (20%), and Logistics (20%).

### 2. User Flow
```
User clicks "Risk History"
↓
Route: GET /risk-history
↓
Controller: RiskHistoryController@index
↓
Risk Engine: RiskScoringEngine@recalculateAllCountries()
↓
Database: Select `country_risk_scores` joined with `risk_score_details`
↓
Blade View: resources/views/user/risk_history.blade.php (Renders Category Badges & Weight % breakdown)
```

### 3. Internal Logic
- **Engine**: `App\Services\Internal\RiskScoringEngine`
- **Models**: `CountryRiskScore`, `RiskScoreDetail`, `RiskCategory`, `RiskWeight`
- **Database Tables**: `country_risk_scores`, `risk_score_details`, `risk_categories`, `risk_weights`

### 4. Files Involved
- **Engine**: `app/Services/Internal/RiskScoringEngine.php`
- **Controller**: `app/Http/Controllers/User/RiskHistoryController.php`
- **Command**: `app/Console/Commands/RecalculateRiskCommand.php`
- **Blade**: `resources/views/user/risk_history.blade.php`

---

# FEATURE 9: TELEMETRY & OBSERVABILITY MONITORING DASHBOARD

### 1. Purpose
Provides real-time system metrics for DevOps & system administrators: SLA Success Rate %, Database Ping Latency (ms), Cache Latency (ms), System Memory Consumption (MB), Free Disk Space (GB), and Queue Throughput.

### 2. User Flow
```
Admin navigates to /admin/observability
↓
Route: GET /admin/observability
↓
Middleware: auth, admin
↓
Controller: ObservabilityController@index
↓
Telemetry Probes:
  ├─ DB::connection()->getPdo() -> Measures DB Latency ms
  ├─ Cache::put('obs_test') -> Measures Cache Latency ms
  ├─ memory_get_usage(true) -> Measures PHP Memory MB
  ├─ disk_free_space() -> Measures Available Storage GB
  └─ SyncTracker::all() -> Measures Sync SLA %
↓
Blade View: resources/views/admin/observability/index.blade.php
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\Admin\ObservabilityController`
- **Support Tracker**: `App\Support\SyncTracker`
- **Blade View**: `resources/views/admin/observability/index.blade.php`

### 4. Files Involved
- **Route**: `routes/web.php` (Line 88)
- **Controller**: `app/Http/Controllers/Admin/ObservabilityController.php`
- **Support**: `app/Support/SyncTracker.php`
- **Blade**: `resources/views/admin/observability/index.blade.php`

---

# FEATURE 10: GNEWS INTELLIGENCE FEED

### 1. Purpose
Aggregates geopolitical, trade, and logistics news headlines from the GNews API to analyze sentiment (positive, neutral, negative) and assess geopolitical risk contributions.

### 2. User Flow
```
User views Dashboard or Country Detail
↓
Service: GNewsService@getLatestArticles()
↓
Logic:
  1. Read database `news_articles` first (< 2ms)
  2. If database is empty, trigger `syncAllNews()` via GNews API
  3. If API quota is exceeded or fails, run `NewsArticleSeeder` fallback
↓
Sentiment Analysis: GNewsService@analyzeSentiment(text)
↓
Blade View: News Cards rendered with Sentiment Badges
```

### 3. Internal Logic
- **Service**: `App\Services\External\GNewsService`
- **Model**: `App\Models\NewsArticle`
- **Database Table**: `news_articles`

### 4. Files Involved
- **Service**: `app/Services/External/GNewsService.php`
- **Command**: `app/Console/Commands/SyncNewsCommand.php`
- **Seeder**: `database/seeders/NewsArticleSeeder.php`

---

# FEATURE 11: WATCHLIST & FAVORITES MODULE

### 1. Purpose
Allows logged-in users to bookmark high-priority countries and monitor them on a customized personal watchlist.

### 2. User Flow
```
User clicks "Add to Watchlist" on a Country Card
↓
Route: POST /watchlist/toggle
↓
Controller: WatchlistController@toggle
↓
Database: Insert/Delete in `watchlists` & `watchlist_items`
↓
JSON Response: { status: 'added' | 'removed' }
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\User\WatchlistController`
- **Models**: `Watchlist`, `WatchlistItem`

---

# FEATURE 12: COUNTRY COMPARISON TOOL

### 1. Purpose
Enables side-by-side comparison of 2 to 4 countries across GDP, Risk Index, Inflation, Weather Extremes, and Cargo Port Capacity.

### 2. User Flow
```
User selects Country A & Country B -> Click "Compare"
↓
Route: GET /compare?countries[]=ID&countries[]=SG
↓
Controller: CompareController@index
↓
Repository: Eager loads data for selected ISO codes
↓
Blade View: Side-by-side comparative metric table & Chart.js Radar Chart
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\User\CompareController`
- **Blade View**: `resources/views/user/compare.blade.php`

---

# FEATURE 13: GLOBAL SHIPMENT ROUTE CALCULATOR

### 1. Purpose
Calculates optimal maritime shipping routes between origin and destination cargo ports, estimating transit distance, travel days, and route risk factors.

### 2. User Flow
```
User selects Origin Port (e.g. IDTPP - Tanjung Priok) & Destination Port (e.g. USLAX - Los Angeles)
↓
Route: POST /route/calculate
↓
Controller: RouteController@calculate
↓
Port Model: Reads coordinates & max depth from `ports` table
↓
Algorithm: Great Circle Distance formula + Port Congestion penalty
↓
JSON Response -> Rendered on Leaflet Map Polyline
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\User\RouteController`
- **Model**: `App\Models\Port`

---

# FEATURE 14: USER PROFILE & PREFERENCES

### 1. Purpose
Allows users to update their personal information, change password, and configure notification threshold preferences.

### 2. User Flow
```
User navigates to /profile
↓
Route: GET /profile
↓
Controller: ProfileController@edit -> Blade View: resources/views/user/profile.blade.php
↓
User submits updates -> POST /profile -> ProfileController@update
```

### 3. Internal Logic
- **Controller**: `App\Http\Controllers\User\ProfileController`
- **Model**: `App\Models\User`

---

# FEATURE 15: ADMIN OPERATIONS & SYNC MANAGER PANEL

### 1. Purpose
Empowers administrators to manually trigger data synchronizations, monitor API health, manage failed queue jobs, and adjust risk weights without requiring SSH access.

### 2. User Flow
```
Admin navigates to /admin/operations or /admin/sync
↓
Route: GET /admin/operations
↓
Middleware: auth, admin
↓
Controllers: OperationsController, SyncController, FailedJobsController
↓
Blade Views: resources/views/admin/*
```

### 3. Internal Logic
- **Controllers**: `App\Http\Controllers\Admin\*`
- **Support**: `App\Support\SyncTracker`

---

# FEATURE 16: RAILWAY CONTAINERIZED PRODUCTION DEPLOYMENT

### 1. Purpose
Automates production deployment on Railway using Nixpacks containers, automatic migration execution, database seeding, and Vite asset compilation.

### 2. Startup Sequence Diagram (`railway-start.sh`):
```
Container Boot
     ↓
1. Check & Generate APP_KEY if missing
     ↓
2. Execute Database Migrations (php artisan migrate --force)
     ↓
3. Check Database Master Dataset State (Country::count() > 0 & system_initialized)
   ├─ UNSEEDED => Run `php artisan waypoint:setup` (Synchronously populates all 195 countries, ports, weather, valas & risk scores)
   └─ SEEDED   => Skip setup sequence
     ↓
4. Optimize Caches (config:cache, route:cache, view:cache)
     ↓
5. Launch Web Server on $PORT (php artisan serve --host 0.0.0.0 --port $PORT)
```

### 3. Primary Files:
- **Boot Script**: `railway-start.sh`
- **Build Spec**: `nixpacks.toml`

---

# SUMMARY OF LOG & DEBUGGING LOCATIONS

| Issue Type | File / Log Location | Resolution Command |
| :--- | :--- | :--- |
| **General Exception** | `storage/logs/laravel.log` | `php artisan optimize:clear` |
| **API Failure Log** | `api_logs` MySQL Table | `php artisan waypoint:setup` |
| **Failed Queue Jobs** | `failed_jobs` MySQL Table | Access `/admin/failed-jobs` -> Click **Retry All** |
| **Asset Missing** | `public/build/manifest.json` | `npm run build` |

---
*End of Waypoint Architectural & Developer Guide.*
