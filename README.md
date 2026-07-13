# GSCRIP — Global Supply Chain Risk Intelligence Platform

GSCRIP adalah platform analisis risiko rantai pasok global real-time modern berbasis **Laravel 11**, dirancang untuk mendeteksi, mengevaluasi, dan memvisualisasikan faktor risiko makroekonomi, cuaca ekstrem, fluktuasi valuta asing, sentimen geopolitik, dan infrastruktur pelabuhan secara terintegrasi.

---

## 📋 Daftar Isi
1. [Deskripsi & Tujuan](#-deskripsi--tujuan)
2. [Fitur Utama](#-fitur-utama)
3. [Tech Stack](#-tech-stack)
4. [Arsitektur Sistem](#-arsitektur-sistem)
5. [Daftar API Eksternal](#-daftar-api-eksternal)
6. [System Requirements](#-system-requirements)
7. [Project Structure](#-project-structure)
8. [Panduan Instalasi & Menjalankan Lokal](#-panduan-instalasi--menjalankan-lokal)
9. [Konfigurasi .env](#-konfigurasi-env)
10. [Database & Seeder](#-database--seeder)
11. [Artisan Commands & Scheduler](#-artisan-commands--scheduler)
12. [Production Deployment Notes](#-production-deployment-notes)
13. [Kredensial Login](#-kredensial-login)
14. [Known Limitations](#-known-limitations)
15. [Dokumentasi Visual (Screenshots)](#-dokumentasi-visual-screenshots)
16. [Roadmap Pengembangan](#-roadmap-pengembangan)
17. [Lisensi](#-lisensi)

---

## 🎯 Deskripsi & Tujuan

Dalam rantai pasok global, disrupsi kecil di pelabuhan transit atau anomali cuaca ekstrem di negara asal komoditas dapat memicu kerugian finansial yang signifikan bagi bisnis logistik dan pengadaan. 

**GSCRIP** dikembangkan untuk:
*   Mengkonsolidasikan metrik geopolitik, makroekonomi, cuaca, logistik, dan forex dalam satu dasbor pusat.
*   Memberikan indeks skor risiko komposit terhitung otomatis per negara untuk pengambilan keputusan sourcing strategis.
*   Menjamin ketahanan sistem tingkat tinggi (high availability) menggunakan pola integrasi asinkron, caching, dan fallback database lokal.

---

## 🚀 Fitur Utama

1.  **Composite Risk Scoring Engine:** Perhitungan dinamis nilai risiko komposit per negara (skala 0-100) berdasarkan pembobotan kategori risiko logistik, ekonomi, stabilitas valas, anomali cuaca, dan sentimen.
2.  **Interactive Dark Maps:** Peta geospatial mode gelap terintegrasi Leaflet.js untuk plotting koordinat pelabuhan kargo logistik dan stasiun cuaca ekstrem.
3.  **Live Auto-Refresh Dashboard:** Sistem auto-update berkala berbasis native Fetch API polling (tanpa WebSocket / library pihak ketiga) lengkap dengan visual status badge `🟢 LIVE` / `🟠 Offline`.
4.  **Country Comparison Engine:** Analisis perbandingan multi-negara secara side-by-side dilengkapi grafik tren komparatif Chart.js.
5.  **Forex Monitor & Trend Charting:** Memantau 150+ nilai tukar mata uang global terhadap basis USD beserta grafik visualisasi volatilitas harian.
6.  **Geopolitical Sentiment Aggregator:** Mengelompokkan berita geopolitik dunia dan melakukan kalkulasi sentimen (Positif/Netral/Negatif) menggunakan pendekatan lexicon dictionary.
7.  **Sourcing Reports & Export Modules:** Fasilitas preview laporan dan pengunduhan instan dokumen A4 Landscape Executive PDF Briefing serta format database raw CSV.

---

## 💻 Tech Stack

*   **Backend Framework:** Laravel 11.x (PHP 8.2+)
*   **Frontend Engine:** Blade Template Engine, Native JavaScript (ES6), CSS3 Vanilla (sleek dark glassmorphism layout)
*   **Styling & UI Components:** Bootstrap 5.x, Bootstrap Icons
*   **Visual Libraries:** Chart.js 4.x (tren & chart komparasi), Leaflet.js 1.9 (pemetaan spasial)
*   **Database Store:** MySQL / MariaDB (data fallback), Database-backed Cache Store (Redis ready)
*   **Build Tooling:** Vite 5.x, NPM

---

## ⚙ Arsitektur Sistem

Platform GSCRIP menerapkan arsitektur tangguh **Live API → Cache → Database Fallback** untuk melindungi sistem dari kegagalan API eksternal dan menjaga efisiensi pemakaian kuota token API.

```text
               +----------------------------------+
               |         Browser Client           |
               +----------------------------------+
                                |
                   (AJAX Fetch / Page Request)
                                |
                                v
               +----------------------------------+
               |        Laravel Controller        |
               +----------------------------------+
                                |
                                v
               +----------------------------------+
               |          Service Layer           |
               +----------------------------------+
                                |
                 +--------------+--------------+
                 |                             |
                 v                             v
      +--------------------+         +-------------------+
      |  Cache Store (Hit) |         | Database Fallback |
      +--------------------+         +-------------------+
                 |                             ^
           (Cache Valid)                  (API Failed)
                 |                             |
                 |                             |
                 v                             |
        [Return Cached Data]                   |
                 |                             |
                 +-----------------------------+
                               |
                        (Cache Invalid / Miss)
                               |
                               v
                     +-------------------+
                     | External REST API |
                     +-------------------+
```

---

## 🌐 Daftar API Eksternal

1.  **REST Countries:** Integrasi data profil geopolitik negara, bendera resmi, mata uang lokal, dan koordinat garis lintang/bujur utama.
2.  **World Bank API:** Aggregasi data makroekonomi tahunan (GDP nominal, laju inflasi tahunan, total populasi penduduk).
3.  **Open-Meteo API:** Sinkronisasi parameter kondisi cuaca global (suhu, kelembaban, hembusan angin, anomali ekstrim).
4.  **ExchangeRate API:** Sinkronisasi berkala harian untuk 150+ kurs mata uang asing terhadap USD.
5.  **GNews API:** Menyediakan feed berita global terbaru terkait komoditas, makro, dan logistik untuk analisis sentimen berbasis kamus leksikon.

---

## 💻 System Requirements

Sebelum menjalankan atau melakukan instalasi sistem, pastikan server atau workstation Anda memenuhi spesifikasi berikut:
*   **PHP:** Versi `>= 8.2` (dengan ekstensi `pdo_mysql`, `curl`, `mbstring`, `openssl`, `xml`, `zip`)
*   **Composer:** Versi `>= 2.2`
*   **Node.js:** Versi `>= 18.x` & **NPM** Versi `>= 9.x`
*   **Database:** MySQL `>= 8.0` atau MariaDB `>= 10.4`
*   **Browser:** Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari versi terbaru (mendukung ES6 JavaScript & Leaflet CSS)

---

## 📂 Project Structure

Berikut adalah visualisasi struktur direktori utama beserta fungsinya dalam platform GSCRIP:
```text
app/
├── Console/
│   └── Commands/             # Artisan commands untuk sinkronisasi terjadwal & manual
├── DTOs/                     # Data Transfer Objects untuk parsing terstruktur data API
├── Http/
│   ├── Controllers/
│   │   ├── Admin/            # Admin console controllers (Users, API health, Audit logs)
│   │   └── User/             # User dashboard controllers (Weather, Currency, Compare, Watchlists)
│   └── Middleware/           # Middleware hak akses role admin & operator
├── Interfaces/               # Decoupled Repository interfaces (kontrak database)
├── Models/                   # Eloquent models (Country, Port, RiskScore, dll)
├── Repositories/             # Implementasi query database (Repository Pattern)
└── Services/                 # Business logic, client API integrations, & scoring engines
config/
└── gscrip.php                # Konfigurasi system limits, API base URL, & TTL Cache
database/
├── migrations/               # Skema tabel database mysql (18 tabel)
└── seeders/                  # Seeder admin, port logistik, bobot risiko default
resources/
├── css/
│   └── app.css               # Desain custom tema gelap premium (glassmorphism)
└── views/
    ├── admin/                # View admin settings (User, API Health, Audit trails)
    ├── layouts/              # Master layout template (app & partials)
    └── user/                 # View dashboard user, compare, maps, charts, watchlists
routes/
├── web.php                   # Registrasi HTTP web & Live AJAX routes
└── console.php               # Registrasi scheduler cron jobs Laravel
```

---

## 🔧 Panduan Instalasi & Menjalankan Lokal

### 1. Kloning Repositori & Install Dependensi
```bash
# Kloning repository
git clone https://github.com/RifqiAqil-ops/global-supply.git
cd global-supply

# Install dependensi PHP (Composer)
composer install

# Install dependensi JavaScript & Assets (NPM)
npm install
```

### 2. Salin Konfigurasi Environment & Generate Key
```bash
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi Kunci API GNews (.env)
Buka file `.env` dan masukkan kunci API GNews Anda (daftar gratis di [GNews.io](https://gnews.io)):
```env
GNEWS_API_KEY="kunci_gnews_anda"
```

### 4. Jalankan Migrasi Database & Seeders
Pastikan Anda telah membuat database kosong di MySQL (misal bernama `gscrip`), konfigurasikan detail database di `.env`, lalu jalankan:
```bash
php artisan migrate --seed
```

### 5. Jalankan Sinkronisasi Data Awal
Guna memuat data spasial, ekonomi, valas, berita, dan menghitung indeks risiko komposit awal ke database, jalankan commands berikut secara berurutan:
```bash
php artisan gscrip:sync-countries
php artisan gscrip:sync-worldbank
php artisan gscrip:sync-weather
php artisan gscrip:sync-exchange
php artisan gscrip:sync-news
php artisan gscrip:recalculate-risk
```

### 6. Jalankan Server Pengembangan Lokal
Terminal 1 (PHP Server):
```bash
php artisan serve
```
Terminal 2 (Vite Server):
```bash
npm run dev
```
Buka browser dan navigasikan ke: `http://localhost:8000`

---

## 📝 Konfigurasi .env

Berikut adalah parameter konfigurasi khusus untuk platform GSCRIP di dalam file `.env`:
```env
APP_NAME="GSCRIP"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Konfigurasi Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gscrip
DB_USERNAME=root
DB_PASSWORD=

# Cache Store (Disarankan database untuk fallback local state)
CACHE_STORE=database
SESSION_DRIVER=database

# API URLs & Keys
OPEN_METEO_BASE_URL=https://api.open-meteo.com/v1
WORLD_BANK_BASE_URL=https://api.worldbank.org/v2
REST_COUNTRIES_BASE_URL=https://raw.githubusercontent.com/mledoze/countries/master
EXCHANGERATE_BASE_URL=https://api.exchangerate-api.com/v4
GNEWS_BASE_URL=https://gnews.io/api/v4
GNEWS_API_KEY="kunci_api_gnews_anda_di_sini"
```

---

## 🗄 Database & Seeder

### Skema Tabel Utama
Sistem ini menggunakan 18 tabel database termigrasi. Beberapa tabel penting:
*   `countries`: Menyimpan profil 250 negara berdaulat.
*   `ports`: Menyimpan koordinat geospatial pelabuhan logistik.
*   `economic_indicators`: Menyimpan histori GDP dan inflasi tahunan.
*   `weather_data`: Menyimpan histori parameter suhu, kelembaban, dan status cuaca ekstrim.
*   `exchange_rates`: Menyimpan data live valas harian.
*   `news_articles`: Menyimpan aggregate artikel geopolitik dan skor sentimen analisis.
*   `country_risk_scores` & `risk_score_details`: Menyimpan hasil kalkulasi risk engine.
*   `watchlists` & `watchlist_items`: Menyimpan custom threshold monitoring milik pengguna.
*   `api_logs`: Audit trail detail latency API eksternal (sumber halaman API Health).
*   `activity_logs`: Audit trail aktivitas administrative operator (sumber halaman Audit Trails).

### Seeders
*   `AdminUserSeeder`: Membuat kredensial default admin dan user.
*   `RiskCategorySeeder`: Membuat 5 kategori risiko utama.
*   `RiskWeightSeeder`: Menerapkan bobot awal 20% merata untuk semua kategori risiko.
*   `PortSeeder`: Menyiapkan dataset koordinat geospatial 21 pelabuhan strategis dunia.
*   `SystemConfigSeeder`: Konfigurasi batasan system threshold dan TTL cache.

---

## 🛠 Artisan Commands & Scheduler

### Artisan Commands
*   `php artisan gscrip:sync-countries` — Menarik profil 250 negara dari REST Countries.
*   `php artisan gscrip:sync-worldbank` — Menarik indikator makroekonomi dari World Bank.
*   `php artisan gscrip:sync-weather` — Menarik anomali cuaca stasiun dunia dari Open-Meteo.
*   `php artisan gscrip:sync-exchange` — Menarik rate valas dari ExchangeRate API.
*   `php artisan gscrip:sync-news` — Menarik artikel dan melakukan lexicon sentiment analysis dari GNews.
*   `php artisan gscrip:recalculate-risk` — Memicu kalkulasi ulang indeks risiko komposit per negara.

### Scheduler (Setiap 1 menit Schedule Runner)
Dalam rute `routes/console.php`, scheduler Laravel dikonfigurasi berjalan berkala dengan proteksi overlapping:
```php
Schedule::command('gscrip:sync-weather')->hourly()->withoutOverlapping();
Schedule::command('gscrip:sync-exchange')->daily()->withoutOverlapping();
Schedule::command('gscrip:sync-news')->hourly()->withoutOverlapping();
Schedule::command('gscrip:recalculate-risk')->hourly()->withoutOverlapping();
```

---

## 🚀 Production Deployment Notes

Saat melakukan deployment platform GSCRIP ke lingkungan produksi (hosting/VPS), pastikan poin-poin berikut diatur:
1.  **Scheduler Laravel:** Jalankan schedule runner di cron server Anda untuk memicu jobs berkala:
    ```bash
    * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
    ```
2.  **Optimasi Cache & Route:** Percepat pemuatan sistem dengan melakukan caching config, routes, dan views:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```
3.  **Queue Runner:** Jalankan supervisor worker untuk menangani antrean background process secara asinkron:
    ```bash
    php artisan queue:work --queue=default --sleep=3 --tries=3
    ```
4.  **Environment Variables:** Pastikan `APP_DEBUG` diatur ke `false` dan database caching diaktifkan menggunakan Redis/Memcached pada server produksi untuk throughput latensi optimal.

---

## 🔑 Kredensial Login

*   **Administrator Account:**
    *   *Email:* `admin@gscrip.com`
    *   *Password:* `password`
*   **Operator User Account:**
    *   *Email:* `user@gscrip.com`
    *   *Password:* `password`

---

## ⚠ Known Limitations

*   **Kunci API GNews:** Modul Geopolitical News memerlukan token API eksternal dari GNews. Jika tidak disediakan, halaman berita akan menampilkan status peringatan penyiapan data konfigurasi (*empty state*).
*   **Direktori Pelabuhan:** Informasi koordinat geografis pelabuhan logistik menggunakan subset database pelabuhan terpilih (curated port dataset 21 pelabuhan utama dunia).
*   **Publikasi Data Bank Dunia:** Indikator ekonomi makro seperti GDP dan laju inflasi dari World Bank mengikuti siklus publikasi resmi, sehingga data terintegrasi biasanya diperbarui secara tahunan.

---

## 📸 Dokumentasi Visual (Screenshots)

Berikut adalah representasi tata letak visual antarmuka platform GSCRIP:

### 1. Dashboard Ringkasan Risiko Global (User Dashboard)
![User Dashboard](docs/screenshots/dashboard.png)

### 2. Sourcing Watchlists & Form Alert Threshold
![Sourcing Watchlists](docs/screenshots/watchlist.png)

### 3. Peta Interaktif Leaflet Cuaca Ekstrim
![Weather Alerts Map](docs/screenshots/weather.png)

### 4. Admin API Health logs & Diagnostik
![API Health Monitor](docs/screenshots/admin-api-health.png)

### 5. Admin Audit Trail logs & Diff Viewer
![Admin Audit Trails](docs/screenshots/admin-audit-trails.png)

---

## 🗺 Roadmap Pengembangan

*   **Fase 1 (Selesai):** Inisiasi skema database, seeder negara, and backend client REST integrations.
*   **Fase 2 (Selesai):** Pembuatan risk scoring engine, modul pelaporan, dan ekspor Landscape PDF.
*   **Fase 3 (Selesai):** Integrasi peta spasial cuaca Leaflet.js dan grafik Chart.js forex/ekonomi.
*   **Fase 4 (Selesai):** Penambahan Live Auto-refresh Polling asinkron dan status Live badges.
*   **Fase 5 (Selesai):** Implementasi CRUD Watchlist, pembenahan halaman admin User/API/Audit, dan penanganan empty state penyiapan GNews.
*   **Fase 6 (Mendatang):** Penerapan notifikasi real-time via email/slack webhook untuk alert threshold terlampaui.

---

## 📄 Lisensi

Platform GSCRIP didistribusikan di bawah lisensi **MIT**. Silakan gunakan, modifikasi, dan distribusikan secara bebas untuk kebutuhan komersial maupun akademis.
