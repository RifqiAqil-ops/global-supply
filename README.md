# GSCRIP

Global Supply Chain Risk Intelligence Platform

GSCRIP is a real-time risk intelligence platform designed to evaluate, monitor, and visualize supply chain vulnerabilities across global sourcing regions. By integrating macroeconomic indicators, extreme weather anomalies, foreign exchange stability, geopolitical news sentiments, and logistics hub coordinates, the platform calculates automated risk profiles to support strategic sourcing decisions.

## Features

- Global Dashboard: Aggregates key risk indicators, news feeds, currency volatility, and weather conditions in a unified control panel.
- Country Intelligence: Individual profiles displaying composite risk indexes, population, capital, and localized risk scores.
- Weather Monitoring: Live meteorological tracking across key countries with automated alerts for extreme anomalies.
- Currency Monitoring: Exchange rate tracking for 150+ international currencies against the USD base with daily volatility charts.
- Risk Scoring Engine: Algorithmic risk calculations integrating economic, weather, geopolitical, and logistical categories.
- Port Intelligence: Interactive cargo port locator containing UN/LOCODE, geographical coordinates, and regional details.
- News Intelligence: Aggregated global news feed categorized with custom sentiment mapping (Positive, Neutral, Negative).
- Watchlists: Sourcing watchlist profiles with customizable alert thresholds and notes.
- Reports Console: Export executive summaries and data directly.
- Universal Search: Keyboard-accessible search capsule (Ctrl+K) supporting fuzzy matching, ranking, history tracking, and shortcuts.
- Admin Control Panel: Administrative dashboard to update risk scoring weights and diagnostic APIs.
- REST API: Exposed endpoints returning JSON data models for ports, weather, and risk metrics.
- PDF Export: Landscape A4 executive briefings formatted in Bahasa Indonesia.
- Excel Export: Native Microsoft Excel spreadsheet (.xlsx) generation with proper data typing and auto-sized column widths.

## Technology

- Framework: Laravel 12.x
- Language: PHP 8.2+, JavaScript (ES6)
- Frontend: Blade Engine, Vanilla CSS3, Bootstrap 5
- Visualizations: Chart.js 4.x (trend graphing), Leaflet.js 1.9 (spatially-indexed map plots)
- Database: MySQL / MariaDB
- Package Manager: Composer, NPM

## Installation

Follow these steps to set up the project locally:

1. Clone the repository and navigate to the project directory:
   ```bash
   git clone <repository-url>
   cd gscrip
   ```

2. Install backend dependencies via Composer:
   ```bash
   composer install
   ```

3. Install frontend assets via NPM:
   ```bash
   npm install
   ```

4. Create and configure the environment variables file:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Run database migrations and seed default values:
   ```bash
   php artisan migrate --seed
   ```

6. Compile frontend assets:
   ```bash
   npm run build
   ```

7. Run the local development server:
   ```bash
   php artisan serve
   ```

## Screenshots

*(Screenshots will be populated here during repository documentation)*

## Project Structure

```text
app/
 ├── DTOs/                      # Data Transfer Objects for API integrations
 ├── Exports/                   # Maatwebsite Excel export structures
 ├── Http/
 │    ├── Controllers/          # Page request and API endpoint controller classes
 │    └── Middleware/           # User authentication and admin guards
 ├── Models/                    # Eloquent database mapping models
 ├── Repositories/              # Database querying layers
 └── Services/                  # Business logic engines and API integrations
database/
 ├── migrations/                # Database schema blueprints
 └── seeders/                   # Initial records population
resources/
 ├── css/                       # Custom styling sheets (app.css)
 ├── js/                        # Core JavaScript files
 └── views/                     # Blade page templates
routes/
 ├── web.php                    # Web browser route definitions
 └── api.php                    # Public REST API route definitions
```

## REST API

The platform provides a public API namespace to expose sourcing telemetry:

### 1. Get Countries List
- **Endpoint**: `/api/countries`
- **Method**: `GET`
- **Description**: Returns all registered countries with details and composite risk ratings.

### 2. Get Country Risk Score
- **Endpoint**: `/api/risk`
- **Method**: `GET`
- **Description**: Returns active risk score evaluations filtered by composite weight variables.

### 3. Get Active Ports
- **Endpoint**: `/api/ports`
- **Method**: `GET`
- **Description**: Returns cargo port coordinates, UN/LOCODE codes, and active statuses.

### 4. Get Geopolitical News
- **Endpoint**: `/api/news`
- **Method**: `GET`
- **Description**: Returns news article listings with title, description, and sentiment ratings.

### 5. Get Exchange Rates
- **Endpoint**: `/api/currency`
- **Method**: `GET`
- **Description**: Returns live exchange rates against the USD base.

## Production Features

- **API Cache**: Implements Cache Store integrations on external API client calls to minimize token depletion and network overhead.
- **Scheduler**: Automates routine weather syncing, currency rate updates, and news aggregation.
- **Risk Engine**: Automated calculations trigger on data update intervals.
- **Universal Search**: Client-side localStorage caching holds port and country indices for instant queries.
- **Responsive UI**: CSS variables enable responsive UI structures across phone, tablet, and desktop viewports.
- **Export PDF**: Fully typeset report briefings localized in Bahasa Indonesia.
- **Export Excel**: Uses native Excel spreadsheet structures preserving numeric column precision.

## License

This project is licensed under the MIT License.
