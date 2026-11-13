# Monitoring Otto

Aplikasi web berbasis **Laravel 11** untuk pemantauan transaksi parkir, rekonsiliasi data multi-sumber (**Admin Otto**, **Settlement MDD**, dan **Report Lokasi**), manajemen master lokasi, serta monitoring status online perangkat di lapangan.

---

## Ringkasan Arsitektur

Aplikasi ini dapat berjalan dalam dua mode konfigurasi:

### 1. Full-API Mode (Default Aktif)
Pada `.env` saat ini, konfigurasi database lokal (`# DB_CONNECTION=mysql`) dinonaktifkan (commented out) sehingga aplikasi beroperasi langsung terhadap API remote di cPanel:
- **Konfigurasi Remote API**: `MONITORING_API_URL` dan `MONITORING_API_TOKEN` (dikelola melalui `config/services.php` pada key `monitoring_api`).
- **Session & Auth Fallback**: Menggunakan `SESSION_DRIVER=file` serta custom provider `custom_eloquent` (`App\Auth\CustomUserProvider` yang didaftarkan oleh `App\Providers\AppServiceProvider`) dengan fallback `session('logged_user')` dan verifikasi remote `/login.php`.
- **Sinkronisasi Tabel cPanel**: Perubahan skema pada `database/migrations/` menjadi referensi DDL untuk dijalankan di **phpMyAdmin cPanel**. **Jangan** menjalankan `php artisan migrate` secara lokal saat `DB_CONNECTION` masih nonaktif.

### 2. Local DB Mode (Opsional)
Jika ingin mengaktifkan database MySQL/SQLite lokal:
1. Aktifkan konfigurasi `DB_CONNECTION` dan kredensial database di `.env`.
2. Jalankan migrasi tabel:
   ```bash
   php artisan migrate
   ```
3. Beberapa controller/service (`TransactionController`, `MonitoringController`, `CustomUserProvider`) mendukung fallback lokal apabila database lokal tersedia.

---

## Tech Stack

- **Backend**: PHP `^8.2`, Laravel Framework `^11.0`, Laravel Sanctum `^4.0`
- **Frontend**: Blade Templates, Tailwind CSS `^4.0` (`@tailwindcss/vite`), Vite `^7.0.7`, Axios `^1.11.0`
- **Dev & Testing**: PHPUnit `^11.0`, Concurrently `^9.0.1`

---

## Menjalankan Proyek Secara Lokal

### Prasyarat
- PHP >= 8.2, Composer, Node.js & npm
- File `.env` dengan `MONITORING_API_URL` dan `MONITORING_API_TOKEN` (gunakan `.env.example` sebagai acuan awal dan isi konfigurasi monitoring API tanpa menuliskan token ke git).

### Instalasi & Menjalankan Server Dev
```bash
# Install dependensi PHP & JS (atau gunakan script composer setup jika memakai DB lokal)
composer install
npm install

# Jalankan server Laravel & Vite secara bersamaan
composer dev
```

Untuk build asset produksi:
```bash
npm run build
```

Untuk menjalankan unit/feature test PHPUnit:
```bash
composer test
```

---

## Fitur & Struktur Direktori Penting

| Area | File / Direktori Utama | Penjelasan Singkat |
| :--- | :--- | :--- |
| **Main Menu / Rekonsiliasi** | `app/Http/Controllers/DashboardController.php`<br>`app/Http/Controllers/Api/MonitoringController.php`<br>`app/Services/ReconciliationService.php`<br>`app/Services/LocationService.php`<br>`resources/views/pages/dashboard/` | Menampilkan perbandingan nominal harian **Admin Otto**, **Settlement MDD**, dan **Report Lokasi** beserta status kecocokannya. `ReconciliationService` mengambil ketiga sumber secara paralel menggunakan `Http::pool()` dan default 30 hari terakhir saat filter kosong. |
| **Transaksi & Sumber Data** | `app/Http/Controllers/Api/TransactionController.php`<br>`app/Models/Transaction.php`<br>`app/Models/SumberTransaksi.php`<br>`database/migrations/2026_11_11_*` | Proxy endpoint `GET`/`POST /api/transactions` serta pemetaan `sumber_id` (`1` = Report Lokasi & Admin Otto, `2` = Settlement MDD). |
| **Master Lokasi & Token** | `app/Http/Controllers/LokasiController.php`<br>`app/Http/Controllers/Api/LokasiApiController.php`<br>`app/Services/RemoteLokasiService.php`<br>`app/Http/Requests/LokasiRequest.php`<br>`app/Http/Resources/LokasiResource.php`<br>`resources/views/pages/lokasi/partials/lokasi-modal.blade.php` | Pengelolaan daftar lokasi beserta URL & token per sumber (`api_token_otto`, `api_token_mdd`, `api_token_lokasi`, `api_url_otto`, `api_url_mdd`, `api_url_lokasi`). |
| **Online Monitoring** | `app/Http/Controllers/OnlineMonitoringController.php`<br>`app/Http/Controllers/Api/OnlineMonitoringApiController.php`<br>`app/Services/OnlineMonitoringService.php`<br>`routes/console.php` | Memantau perangkat online/gangguan/offline menggunakan remote endpoint `/online_monitoring.php` dengan ambang batas `300s` (online), `600s` (gangguan), dan periode penuh `86400s`, serta command `app:status-online-monitoring`. |
| **Autentikasi** | `app/Http/Controllers/AuthController.php`<br>`app/Http/Controllers/Api/AuthApiController.php`<br>`app/Auth/CustomUserProvider.php`<br>`resources/views/pages/auth/login.blade.php` | Autentikasi web & API (`statefulApi()` di `bootstrap/app.php`) dengan fallback login remote. |
| **Fallback Config Default** | `config/monitoring.php` | Data fallback `default_locations` dan `network_devices`. |

---

## Dokumentasi & Kustomisasi Agent

Proyek ini dilengkapi aturan workspace dan skill khusus untuk AI coding agent serta referensi API:

- [AGENTS.md](./AGENTS.md) — Aturan utama workspace, konvensi arsitektur Full-API, batasan environment, dan peta file proyek.
- [docs/API_ENDPOINTS.md](./docs/API_ENDPOINTS.md) — Daftar lengkap endpoint RESTful Laravel (`/api/*`) dan cPanel (`/api/*`), parameter filter query, serta format payload transaksi.
- [.agents/skills/reconciliation-api/SKILL.md](./.agents/skills/reconciliation-api/SKILL.md) — Panduan detail alur rekonsiliasi Menu Main dan query transaksi multi-sumber.
- [.agents/skills/cpanel-api-sync/SKILL.md](./.agents/skills/cpanel-api-sync/SKILL.md) — Panduan sinkronisasi endpoint PHP cPanel, aturan `.htaccess`, Full-API fallback, dan paritas skema MySQL.
