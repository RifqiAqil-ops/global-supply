<!DOCTYPE html>
<html lang="en" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Global Supply Chain Intelligence') | Waypoint</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body style="background-color: var(--color-bg); color: var(--color-text-main);">

    @include('layouts.partials.bootstrap_banner')

    <!-- Top Navbar -->
    @include('layouts.partials.navbar')

    <!-- Main Content Area with offset for fixed top nav -->
    <div class="content-wrapper-offset">
        <main class="flex-grow-1 p-4">
            <!-- Breadcrumb Section -->
            @include('layouts.partials.breadcrumbs')

            <!-- Flash Status Alert Banner -->
            @include('layouts.partials.flash')

            <!-- Dynamic Page Yield -->
            @yield('content')
        </main>

        <!-- Sticky Footer Area -->
        @include('layouts.partials.footer')
    </div>

    <!-- Global AJAX Polling & Live Auto Refresh Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const syncDot = document.getElementById('liveSyncDot');
            const syncText = document.getElementById('liveSyncText');
            const syncTime = document.getElementById('liveSyncTime');

            function updateTime() {
                const now = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                const timeString = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
                if (syncTime) syncTime.innerText = timeString;
            }

            function setStatusOnline() {
                if (syncDot) {
                    syncDot.classList.remove('live-status-offline');
                }
                if (syncText) {
                    syncText.innerText = '🟢 LIVE';
                    syncText.className = 'live-status-text fw-bold text-success';
                }
                const container = document.getElementById('liveSyncIndicator');
                if (container) {
                    container.className = 'badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 d-flex align-items-center gap-2 px-2.5 py-1.5 rounded';
                }
            }

            function setStatusOffline() {
                if (syncDot) {
                    syncDot.classList.add('live-status-offline');
                }
                if (syncText) {
                    syncText.innerText = '🟠 Offline';
                    syncText.className = 'live-status-text fw-bold text-warning';
                }
                const container = document.getElementById('liveSyncIndicator');
                if (container) {
                    container.className = 'badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 d-flex align-items-center gap-2 px-2.5 py-1.5 rounded';
                }
            }

            updateTime();

            // Helper to secure base paths correctly
            const baseUri = window.location.origin;

            // 1. Dashboard Metrics Polling (Interval: 60s)
            if (document.getElementById('stat-avg-risk')) {
                setInterval(function() {
                    fetch(baseUri + '/live-api/dashboard-metrics')
                        .then(response => response.json())
                        .then(res => {
                            if (res.status === 'success') {
                                setStatusOnline();
                                updateTime();
                                // Update summary cards
                                document.getElementById('stat-avg-risk').innerText = res.data.avgRisk;
                                document.getElementById('stat-countries-count').innerText = res.data.countriesMonitored;
                                document.getElementById('stat-extreme-weather').innerText = res.data.extremeWeatherCount;
                                document.getElementById('stat-currencies-count').innerText = res.data.currenciesCount;

                                // Update Top Risk Hotspots
                                const hotspotsTbody = document.getElementById('hotspots-tbody');
                                if (hotspotsTbody && res.data.topRiskCountries) {
                                    let html = '';
                                    res.data.topRiskCountries.forEach(tr => {
                                        const trendIcon = tr.score_change > 0 ? 'bi-arrow-up' : (tr.score_change < 0 ? 'bi-arrow-down' : 'bi-dash');
                                        const trendColor = tr.score_change > 0 ? 'text-danger' : (tr.score_change < 0 ? 'text-success' : 'text-muted');
                                        const trendText = tr.score_change > 0 ? 'Rising' : (tr.score_change < 0 ? 'Lower' : 'Stable');
                                        const badgeColor = tr.risk_level === 'High' || tr.risk_level === 'Critical' ? 'danger' : (tr.risk_level === 'Medium' ? 'warning' : 'success');
                                        
                                        html += `
                                            <tr>
                                                <td>
                                                    <a href="/countries/${tr.iso2}" class="d-flex align-items-center gap-2 small text-white text-decoration-none hover-primary">
                                                        <img src="${tr.country_flag}" alt="" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                                                        ${tr.country_name}
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor} border border-${badgeColor} border-opacity-20 small px-2 py-1 rounded">
                                                        ${tr.composite_score} (${tr.risk_level})
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="${trendColor} small">
                                                        <i class="bi ${trendIcon}"></i> ${trendText}
                                                    </span>
                                                </td>
                                            </tr>
                                        `;
                                    });
                                    hotspotsTbody.innerHTML = html;
                                }

                                // Update Top 10 Highest Risk
                                const highestRiskTbody = document.getElementById('highest-risk-tbody');
                                if (highestRiskTbody && res.data.topHighestRisk) {
                                    let html = '';
                                    res.data.topHighestRisk.forEach(h => {
                                        const badgeColor = h.risk_level === 'High' || h.risk_level === 'Critical' ? 'danger' : (h.risk_level === 'Medium' ? 'warning' : 'success');
                                        html += `
                                            <tr>
                                                <td>
                                                    <a href="/countries/${h.iso2}" class="d-flex align-items-center gap-2 small text-white text-decoration-none hover-primary">
                                                        <img src="${h.country_flag}" alt="" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                                                        ${h.country_name}
                                                    </a>
                                                </td>
                                                <td><strong class="text-white">${h.composite_score}</strong></td>
                                                <td>
                                                    <span class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor} border border-${badgeColor} border-opacity-20 small px-2 py-1 rounded">
                                                        ${h.risk_level}
                                                    </span>
                                                </td>
                                            </tr>
                                        `;
                                    });
                                    highestRiskTbody.innerHTML = html;
                                }

                                // Update Top 10 Lowest Risk
                                const lowestRiskTbody = document.getElementById('lowest-risk-tbody');
                                if (lowestRiskTbody && res.data.topLowestRisk) {
                                    let html = '';
                                    res.data.topLowestRisk.forEach(l => {
                                        const badgeColor = l.risk_level === 'High' || l.risk_level === 'Critical' ? 'danger' : (l.risk_level === 'Medium' ? 'warning' : 'success');
                                        html += `
                                            <tr>
                                                <td>
                                                    <a href="/countries/${l.iso2}" class="d-flex align-items-center gap-2 small text-white text-decoration-none hover-primary">
                                                        <img src="${l.country_flag}" alt="" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                                                        ${l.country_name}
                                                    </a>
                                                </td>
                                                <td><strong class="text-white">${l.composite_score}</strong></td>
                                                <td>
                                                    <span class="badge bg-${badgeColor} bg-opacity-10 text-${badgeColor} border border-${badgeColor} border-opacity-20 small px-2 py-1 rounded">
                                                        ${l.risk_level}
                                                    </span>
                                                </td>
                                            </tr>
                                        `;
                                    });
                                    lowestRiskTbody.innerHTML = html;
                                }

                                // Update Recent Changes
                                const recentChangesTbody = document.getElementById('recent-changes-tbody');
                                if (recentChangesTbody && res.data.recentChanges) {
                                    let html = '';
                                    res.data.recentChanges.forEach(rc => {
                                        const diffColor = rc.change > 0 ? 'text-danger' : 'text-success';
                                        const diffIcon = rc.change > 0 ? 'bi-arrow-up' : 'bi-arrow-down';
                                        html += `
                                            <tr>
                                                <td>
                                                    <a href="/countries/${rc.iso2}" class="d-flex align-items-center gap-2 small text-white text-decoration-none hover-primary">
                                                        <img src="${rc.country_flag}" alt="" class="rounded border border-secondary border-opacity-10" style="width: 20px; height: 13px; object-fit: cover;">
                                                        ${rc.country_name}
                                                    </a>
                                                </td>
                                                <td><span class="text-muted">${rc.prev_score}</span></td>
                                                <td><span class="text-white fw-bold">${rc.new_score}</span></td>
                                                <td>
                                                    <span class="${diffColor} fw-semibold small">
                                                        <i class="bi ${diffIcon}"></i> ${rc.change > 0 ? '+' : ''}${rc.change.toFixed(2)}
                                                    </span>
                                                </td>
                                            </tr>
                                        `;
                                    });
                                    recentChangesTbody.innerHTML = html;
                                }

                                // Update Recent Alerts
                                const recentAlertsList = document.getElementById('recent-alerts-list');
                                if (recentAlertsList && res.data.recentAlerts) {
                                    let html = '';
                                    res.data.recentAlerts.forEach(ra => {
                                        html += `
                                            <div class="d-flex gap-3 align-items-start p-2.5 rounded border border-danger border-opacity-10" style="background-color: rgba(220, 53, 69, 0.02);">
                                                <div class="text-danger mt-0.5"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-white small fw-bold">${ra.description}</span>
                                                        <span class="text-muted small" style="font-size: 0.72rem;">${ra.time_ago}</span>
                                                    </div>
                                                    <div class="text-muted small mt-1" style="font-size: 0.72rem;">
                                                        Change details: score shifted from 
                                                        <span class="text-warning fw-semibold">${ra.old_score}</span> 
                                                        to 
                                                        <span class="text-danger fw-semibold">${ra.new_score}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    });
                                    if (html === '') {
                                        html = `
                                            <div class="text-center text-muted py-4">
                                                <i class="bi bi-shield-check display-6 d-block mb-2 text-success"></i>
                                                <p class="small mb-0">No active system alerts recorded.</p>
                                            </div>
                                        `;
                                    }
                                    recentAlertsList.innerHTML = html;
                                }
                            } else {
                                setStatusOffline();
                            }
                        })
                        .catch(() => setStatusOffline());
                }, 60000);
            }

            // 2. Weather Dashboard Polling (Interval: 30s)
            if (document.getElementById('stat-weather-stations')) {
                setInterval(function() {
                    fetch(baseUri + '/live-api/weather')
                        .then(response => response.json())
                        .then(res => {
                            if (res.status === 'success') {
                                setStatusOnline();
                                updateTime();

                                document.getElementById('stat-weather-stations').innerText = res.data.totalStations;
                                document.getElementById('stat-weather-alerts').innerText = res.data.extremeCount;
                                document.getElementById('stat-weather-avg-temp').innerText = res.data.avgTemp + '°C';
                                document.getElementById('stat-weather-avg-humidity').innerText = res.data.avgHumidity + '%';

                                // Update Table
                                const weatherTbody = document.getElementById('weather-table-tbody');
                                if (weatherTbody && res.data.entries) {
                                    let html = '';
                                    res.data.entries.forEach(w => {
                                        const badgeType = w.is_extreme ? 'danger' : 'success';
                                        const badgeText = w.is_extreme ? '⚠ Extreme' : 'Normal';
                                        html += `
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2 small">
                                                        <img src="${w.country_flag}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">
                                                        <a href="/countries/${w.iso2}" class="text-white text-decoration-none hover-primary">${w.country_name}</a>
                                                    </div>
                                                </td>
                                                <td class="text-white fw-semibold">${w.temperature}°C</td>
                                                <td class="text-muted small">${w.feels_like}°C</td>
                                                <td class="text-muted small">${w.humidity}%</td>
                                                <td class="text-muted small">${w.wind_speed} km/h</td>
                                                <td class="text-muted small">${w.precipitation} mm</td>
                                                <td class="text-muted small">${w.uv_index}</td>
                                                <td class="text-muted small">${w.weather_description}</td>
                                                <td>
                                                    <span class="badge bg-${badgeType} bg-opacity-10 text-${badgeType} border border-${badgeType} border-opacity-20 small px-2 py-1 rounded">
                                                        ${badgeText}
                                                    </span>
                                                </td>
                                                <td class="text-muted small">${w.fetched_at}</td>
                                            </tr>
                                        `;
                                    });
                                    weatherTbody.innerHTML = html;
                                }

                                // Update Leaflet Map Markers
                                if (window.updateWeatherMarkers && res.data.mapMarkers) {
                                    window.updateWeatherMarkers(res.data.mapMarkers);
                                }
                            } else {
                                setStatusOffline();
                            }
                        })
                        .catch(() => setStatusOffline());
                }, 30000);
            }

            // 3. Currency Dashboard Polling (Interval: 30s)
            if (document.getElementById('stat-currency-tracked')) {
                setInterval(function() {
                    fetch(baseUri + '/live-api/exchange-rates')
                        .then(response => response.json())
                        .then(res => {
                            if (res.status === 'success') {
                                setStatusOnline();
                                updateTime();

                                document.getElementById('stat-currency-tracked').innerText = res.data.totalCurrencies;
                                document.getElementById('stat-currency-avg-change').innerText = res.data.avgChange;
                                document.getElementById('stat-currency-gainers').innerText = res.data.topGainers.length;
                                document.getElementById('stat-currency-losers').innerText = res.data.topLosers.length;

                                // Update Gainers List
                                const gainersList = document.getElementById('dashboard-gainers-list');
                                if (gainersList && res.data.topGainers) {
                                    let html = '';
                                    res.data.topGainers.slice(0, 5).forEach(g => {
                                        html += `
                                            <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded" style="background: rgba(34,197,94,0.05);">
                                                <div class="d-flex align-items-center gap-2">
                                                    ${g.country_flag ? `<img src="${g.country_flag}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">` : ''}
                                                    <span class="text-white fw-semibold small">${g.currency_code}</span>
                                                </div>
                                                <span class="text-success fw-bold small">+${g.change_percent}%</span>
                                            </div>
                                        `;
                                    });
                                    if (html === '') html = '<span class="text-muted small">No gainers data available</span>';
                                    gainersList.innerHTML = html;
                                }

                                // Update Losers List
                                const losersList = document.getElementById('dashboard-losers-list');
                                if (losersList && res.data.topLosers) {
                                    let html = '';
                                    res.data.topLosers.slice(0, 5).forEach(l => {
                                        html += `
                                            <div class="d-flex justify-content-between align-items-center px-2 py-1 rounded" style="background: rgba(239,68,68,0.05);">
                                                <div class="d-flex align-items-center gap-2">
                                                    ${l.country_flag ? `<img src="${l.country_flag}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">` : ''}
                                                    <span class="text-white fw-semibold small">${l.currency_code}</span>
                                                </div>
                                                <span class="text-danger fw-bold small">${l.change_percent}%</span>
                                            </div>
                                        `;
                                    });
                                    if (html === '') html = '<span class="text-muted small">No losers data available</span>';
                                    losersList.innerHTML = html;
                                }

                                // Update Table
                                const ratesTbody = document.getElementById('exchange-rates-tbody');
                                if (ratesTbody && res.data.rates) {
                                    let html = '';
                                    res.data.rates.forEach(rate => {
                                        const changeHtml = rate.change_percent !== null 
                                            ? `<span class="${parseFloat(rate.change_percent) >= 0 ? 'text-success' : 'text-danger'} fw-semibold small">
                                                ${parseFloat(rate.change_percent) >= 0 ? '+' : ''}${rate.change_percent}%
                                               </span>`
                                            : '<span class="text-muted">—</span>';
                                        
                                        const rawNum = parseFloat(rate.rate_to_usd.replace(/,/g, ''));
                                        let displayRate = rate.rate_to_usd;
                                        if (rawNum > 0) {
                                            if (rawNum >= 0.01) {
                                                displayRate = rawNum.toFixed(4) + ' USD';
                                            } else {
                                                const formattedInverse = (1 / rawNum).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                                displayRate = `1 USD = ${formattedInverse} ${rate.currency_code}`;
                                            }
                                        }

                                        html += `
                                            <tr>
                                                <td><strong class="text-white">${rate.currency_code}</strong> <span class="text-muted small">${rate.currency_name}</span></td>
                                                <td>
                                                    ${rate.country_name ? `
                                                    <div class="d-flex align-items-center gap-2 small">
                                                        <img src="${rate.country_flag}" class="rounded" style="width:18px;height:12px;object-fit:cover;" alt="">
                                                        ${rate.country_name}
                                                    </div>` : '<span class="text-muted small">—</span>'}
                                                </td>
                                                <td class="text-white fw-semibold">${displayRate}</td>
                                                <td class="text-muted">${rate.rate_to_idr}</td>
                                                <td>${changeHtml}</td>
                                                <td class="text-muted small">${rate.rate_date}</td>
                                            </tr>
                                        `;
                                    });
                                    ratesTbody.innerHTML = html;
                                }

                                // Update Chart.js Trend Chart dynamically without destroying
                                if (window.currencyTrendChartInstance && res.data.rates) {
                                    const datasets = window.currencyTrendChartInstance.data.datasets;
                                    datasets.forEach(dataset => {
                                        const code = dataset.label;
                                        const matchRate = res.data.rates.find(r => r.currency_code === code);
                                        if (matchRate) {
                                            dataset.data.push(parseFloat(matchRate.rate_to_usd.replace(/,/g, '')));
                                            dataset.data.shift();
                                        }
                                    });
                                    window.currencyTrendChartInstance.update();
                                }
                            } else {
                                setStatusOffline();
                            }
                        })
                        .catch(() => setStatusOffline());
                }, 30000);
            }

            // 4. Geopolitical News Polling (Interval: 60s)
            if (document.getElementById('news-articles-grid')) {
                setInterval(function() {
                    fetch(baseUri + '/live-api/news')
                        .then(response => response.json())
                        .then(res => {
                            if (res.status === 'success') {
                                setStatusOnline();
                                updateTime();

                                const newsGrid = document.getElementById('news-articles-grid');
                                if (newsGrid && res.data) {
                                    let html = '';
                                    res.data.forEach(a => {
                                        const sentBadge = a.sentiment === 'negative' ? 'danger' : (a.sentiment === 'positive' ? 'success' : 'secondary');
                                        const imgHtml = a.image_url 
                                            ? `<img src="${a.image_url}" class="card-img-top" style="height: 160px; object-fit: cover; opacity: 0.85;" alt="" onerror="this.style.display='none'">`
                                            : `<div class="card-img-top d-flex align-items-center justify-content-center" style="height: 160px; background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(168,85,247,0.1));">
                                                <i class="bi bi-newspaper display-4 text-muted"></i>
                                               </div>`;

                                        html += `
                                            <div class="col-md-6 col-lg-4">
                                                <div class="card card-premium border-0 h-100" style="transition: transform 0.2s;">
                                                    ${imgHtml}
                                                    <div class="card-body d-flex flex-column">
                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                            ${a.country_flag ? `<img src="${a.country_flag}" class="rounded" style="width:16px;height:11px;object-fit:cover;" alt="">` : ''}
                                                            ${a.country_name ? `<span class="text-muted small">${a.country_name}</span>` : ''}
                                                            <span class="badge bg-${sentBadge} bg-opacity-10 text-${sentBadge} border border-${sentBadge} border-opacity-20 small px-2 py-0.5 rounded">
                                                                ${a.sentiment.charAt(0).toUpperCase() + a.sentiment.slice(1)}
                                                            </span>
                                                        </div>
                                                        <h6 class="text-white fw-bold mb-2" style="line-height: 1.4;">${a.title}</h6>
                                                        <p class="text-muted small mb-3 flex-grow-1">${a.description || ''}</p>
                                                        <div class="d-flex align-items-center justify-content-between mt-auto">
                                                            <span class="text-muted small">
                                                                <i class="bi bi-clock me-1"></i>${a.published_at}
                                                            </span>
                                                            ${a.source_url && !a.source_url.includes('example.com') ? `<a href="${a.source_url}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary py-0 px-2 small"><i class="bi bi-box-arrow-up-right me-1"></i>Source</a>` : `<span class="badge bg-secondary opacity-75 font-monospace text-uppercase" style="font-size: 0.68rem;">Demo Data</span>`}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    });
                                    if (html === '') {
                                        html = `
                                            <div class="col-12">
                                                <div class="card card-premium border-0">
                                                    <div class="card-body text-center py-5">
                                                        <i class="bi bi-newspaper display-4 text-muted d-block mb-3"></i>
                                                        <h5 class="text-white">No News Articles Found</h5>
                                                        <p class="text-muted small mb-0">News articles will appear here once the GNews API feed is configured.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    }
                                    newsGrid.innerHTML = html;
                                }
                            } else {
                                setStatusOffline();
                            }
                        })
                        .catch(() => setStatusOffline());
                }, 60000);
            }

            // 5. Country Detail Risk Breakdown Polling (Interval: 60s)
            const detailCard = document.getElementById('country-detail-risk-breakdown-card');
            if (detailCard) {
                const code = detailCard.getAttribute('data-country-code');
                setInterval(function() {
                    fetch(baseUri + `/live-api/country-risk/${code}`)
                        .then(response => response.json())
                        .then(res => {
                            if (res.status === 'success') {
                                setStatusOnline();
                                updateTime();

                                // Update composite score and timestamp
                                document.getElementById('total-composite-risk-value').innerText = res.data.composite_score;
                                const lastCalc = document.getElementById('risk-last-calculated');
                                if (lastCalc) lastCalc.innerText = res.data.calculated_at;

                                // Update categories
                                if (res.data.details) {
                                    res.data.details.forEach(d => {
                                        const row = document.getElementById(`risk-category-row-${d.category_slug}`);
                                        if (row) {
                                            row.querySelector('.score-val').innerText = d.category_score.toFixed(2);
                                            row.querySelector('.score-weighted').innerText = d.weighted_score.toFixed(2);
                                            
                                            const progBar = row.querySelector('.progress-bar');
                                            if (progBar) {
                                                progBar.style.width = d.category_score + '%';
                                                progBar.setAttribute('aria-valuenow', d.category_score);
                                            }
                                        }
                                    });
                                }
                            } else {
                                setStatusOffline();
                            }
                        })
                        .catch(() => setStatusOffline());
                }, 60000);
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
