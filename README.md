# GSCRIP

Global Supply Chain Risk Intelligence Platform

GSCRIP adalah platform intelijen risiko rantai pasok global real-time modern yang dirancang untuk mendeteksi, mengevaluasi, dan memvisualisasikan faktor risiko makroekonomi, cuaca ekstrem, fluktuasi valuta asing, sentimen geopolitik, dan infrastruktur pelabuhan secara terintegrasi. Platform ini menghitung indeks skor risiko komposit otomatis per negara untuk mendukung pengambilan keputusan sourcing yang strategis.

## Fitur Utama

- Dasbor Global: Mengintegrasikan indikator risiko utama, feed berita geopolitik, volatilitas mata uang, dan kondisi cuaca ekstrem dalam satu panel kendali terpadu.
- Intelijen Negara: Profil individu per negara yang menampilkan indeks risiko komposit, populasi, ibu kota, serta rincian skor risiko sektoral.
- Pemantauan Cuaca: Pemantauan kondisi meteorologi secara langsung di berbagai negara dengan peringatan otomatis untuk anomali cuaca ekstrem.
- Pemantauan Mata Uang: Pelacakan nilai tukar untuk 150+ mata uang internasional terhadap basis USD dilengkapi dengan grafik volatilitas harian.
- Mesin Penghitung Risiko: Kalkulasi indeks risiko berbasis algoritma yang mengintegrasikan kategori ekonomi, cuaca, geopolitik, dan logistik.
- Intelijen Pelabuhan: Peta interaktif pencari pelabuhan kargo logistik yang memuat kode UN/LOCODE, koordinat geografis, serta detail regional.
- Intelijen Berita: Feed berita geopolitik dunia yang dikelompokkan berdasarkan peringkat sentimen (Positif, Netral, Negatif).
- Sourcing Watchlist: Profil daftar pantau komoditas dengan batas ambang batas (alert threshold) peringatan dan catatan remarks yang dapat disesuaikan.
- Konsol Laporan: Penyediaan preview laporan risiko dan pengunduhan instan dokumen.
- Pencarian Universal: Bilah pencarian cepat (Ctrl+K) yang mendukung fuzzy matching, pembobotan skor kecocokan, riwayat pencarian, dan navigasi keyboard penuh.
- Panel Admin: Dasbor khusus admin untuk memperbarui bobot perhitungan kalkulasi risiko dan melakukan diagnosis status API.
- REST API: Menyediakan endpoint publik untuk data negara, skor risiko, pelabuhan kargo, berita, dan nilai mata uang.
- Ekspor PDF: Laporan briefing eksekutif format A4 landscape yang telah dilokalisasi sepenuhnya ke dalam Bahasa Indonesia.
- Ekspor Excel: Unduhan data spreadsheet Microsoft Excel (.xlsx) asli dengan tipe data angka yang presisi dan kolom yang otomatis menyesuaikan lebar data.

## Teknologi

- Framework: Laravel 12.x
- Bahasa: PHP 8.2+, JavaScript (ES6)
- Frontend: Blade Engine, Vanilla CSS3, Bootstrap 5
- Visualisasi: Chart.js 4.x (tren grafik volatilitas), Leaflet.js 1.9 (pemetaan spasial interaktif)
- Database: MySQL / MariaDB
- Pengelola Dependensi: Composer, NPM

## Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal:

1. Clone repositori dan masuk ke direktori proyek:
   ```bash
   git clone <repository-url>
   cd gscrip
   ```

2. Instal dependensi backend menggunakan Composer:
   ```bash
   composer install
   ```

3. Instal aset frontend menggunakan NPM:
   ```bash
   npm install
   ```

4. Buat dan konfigurasikan file environment variables:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Jalankan migrasi database dan seed data awal:
   ```bash
   php artisan migrate --seed
   ```

6. Kompilasi aset frontend:
   ```bash
   npm run build
   ```

7. Jalankan server pengembangan lokal:
   ```bash
   php artisan serve
   ```

## Dokumentasi Visual (Screenshots)

*(Dokumentasi tangkapan layar akan dimuat di bagian ini selama rilis repositori)*

## Struktur Proyek

```text
app/
 ├── DTOs/                      # Data Transfer Objects untuk integrasi API
 ├── Exports/                   # Struktur ekspor data Microsoft Excel
 ├── Http/
 │    ├── Controllers/          # Class controller untuk request halaman & endpoint API
 │    └── Middleware/           # Guard untuk autentikasi user dan admin
 ├── Models/                    # Model pemetaan database Eloquent
 ├── Repositories/              # Layer penulisan query database
 └── Services/                  # Logika bisnis utama dan integrasi API eksternal
database/
 ├── migrations/                # Blueprint skema database
 └── seeders/                   # Pengisian records awal database
resources/
 ├── css/                       # Custom styling sheet (app.css)
 ├── js/                        # File inti JavaScript
 └── views/                     # Template tampilan halaman Blade
routes/
 ├── web.php                    # Definisi rute browser web
 └── api.php                    # Definisi rute REST API publik
```

## REST API

Platform ini menyediakan namespace API publik untuk mengekspos data telemetri:

### 1. Mendapatkan Daftar Negara
- **Endpoint**: `/api/countries`
- **Metode**: `GET`
- **Deskripsi**: Mengembalikan daftar semua negara yang terdaftar beserta detail rating risiko komposit.

### 2. Mendapatkan Skor Risiko
- **Endpoint**: `/api/risk`
- **Metode**: `GET`
- **Deskripsi**: Mengembalikan evaluasi skor risiko aktif yang difilter berdasarkan parameter bobot komposit.

### 3. Mendapatkan Pelabuhan Aktif
- **Endpoint**: `/api/ports`
- **Metode**: `GET`
- **Deskripsi**: Mengembalikan koordinat geografis pelabuhan, kode UN/LOCODE, dan status keaktifan pelabuhan.

### 4. Mendapatkan Berita Geopolitik
- **Endpoint**: `/api/news`
- **Metode**: `GET`
- **Deskripsi**: Mengembalikan daftar artikel berita geopolitik lengkap dengan analisis sentimen.

### 5. Mendapatkan Nilai Tukar Mata Uang
- **Endpoint**: `/api/currency`
- **Metode**: `GET`
- **Deskripsi**: Mengembalikan nilai kurs mata uang global real-time terhadap basis USD.

## Fitur Tingkat Produksi

- **API Cache**: Menerapkan integrasi Cache Store pada pemanggilan client API eksternal untuk menghemat kuota token API dan latensi jaringan.
- **Scheduler**: Mengotomatiskan sinkronisasi cuaca harian, pembaruan kurs mata uang, dan agregasi berita secara berkala.
- **Kalkulasi Otomatis**: Perhitungan bobot risiko baru otomatis berjalan setiap kali data eksternal berhasil disinkronisasikan.
- **Pencarian Cepat**: Data pelabuhan dan profil negara disimpan di client-side localStorage untuk memberikan hasil instan saat diketik.
- **Responsive UI**: Variabel CSS3 modern memastikan grid layout tetap rapi di smartphone, tablet, laptop, hingga layar desktop lebar.
- **Ekspor PDF**: Format laporan A4 eksekutif terjemahan Bahasa Indonesia yang presisi.
- **Ekspor Excel**: Pembuatan spreadsheet asli dengan ketepatan nilai desimal untuk analisis data lebih lanjut di Microsoft Excel.

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT.
