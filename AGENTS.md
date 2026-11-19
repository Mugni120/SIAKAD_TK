# SIAKAD TK

## Local setup

- This repository is a Laravel 12 wrapper around the original framework-free PHP/MySQL application. The original app files stay at the repository root; Laravel only bootstraps and routes requests to them.
- Serve the directory through XAMPP Apache/MySQL and open `http://localhost/siakad_tk/`. The root `.htaccess` sends every request to `public/index.php`, which keeps the original URLs working.
- Install dependencies with `C:\xampp\php\php.exe C:\path\to\composer.phar install`. If `.env` is missing, copy `.env.example` to `.env` and run `C:\xampp\php\php.exe artisan key:generate`.
- Laravel configuration lives in `laravel-config/`. Do not move it into `config/`: Laravel executes every PHP file in its config path, and `config/koneksi.php` is a legacy connection script that must only run when the legacy app includes it.
- The wrapper is `app/Http/Controllers/LegacyController.php` plus `routes/legacy.php`. It is registered without the Laravel `web` middleware so legacy `session_start()`, forms, redirects, and headers behave as before. It allowlists legacy entrypoints and assets; do not replace it with a catch-all that can include arbitrary project files.
- PHP syntax check for the whole repository:
  ```powershell
  Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & 'C:\xampp\php\php.exe' -l $_.FullName }
  ```
  Framework checks: `C:\xampp\php\php.exe artisan about` and `composer audit`.
- Several pages depend on CDN-hosted Bootstrap, Bootstrap Icons, and Google Fonts; there is no local frontend build step.

## Database

- `config/koneksi.php` hard-codes MySQL `localhost`, user `root`, an empty password, and database `data_tk`; the legacy app does not use Laravel's database configuration. Keep the database name in sync with the SQL dump and `.env`.
- `database/data_tk.sql` creates the `data_tk` schema; `database/seed_data.sql` contains optional seed data. There is no migration system for the legacy tables. Import before first use with `Get-Content -Raw .\database\data_tk.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u root`.
- `buat_admin.php` is an exposed development bootstrap page: opening it creates or updates the `Admin` account `mugniAja` with password `mugni123`. Do not use or leave it exposed in a shared deployment.

## Application flow

- `index.php` is the landing page; `login.php` posts to `proses_login.php`, which uses `password_verify` and routes on the exact session role values `Admin`, `Guru`, and `Orang Tua`.
- `admin/`, `guru/`, and `orang_tua/` contain direct PHP page/action scripts rather than framework controllers. Admin CRUD pages use `?page=list|tambah|edit` and `*_aksi.php`; attendance/development actions are POST handlers. A parent first selects a child in `orang_tua/index.php`, which stores `$_SESSION['id_siswa']`.
- Login session data is role-specific: Guru sessions also need `id_guru` and `id_kelas`; parent sessions need `id_siswa`. Keep these names synchronized with the page queries.
- Optional features include Fonnte WhatsApp notifications (`cron/wa_notif_absen.php`), Midtrans payments (`orang_tua/payment_*`), attendance QR scanning, and K-Means clustering in `admin/clustering.php`.

## Change-sensitive quirks

- Authentication is implemented independently in each legacy script; templates do not enforce access. New protected pages/actions must explicitly start the session and check the required role. Existing guard gaps are `admin/pengumuman.php`, `admin/pengumuman_edit.php`, and `orang_tua/absensi.php`—do not copy those patterns.
- Attendance persistence uses the `keterangan[...]` form-field name and the `absensi.keterangan` database column; keep those names aligned if changing the form or action.
- `admin/template/` and `guru/template/` are HTML partials, not shared controllers. `assets/css/style.css` is linked by those templates, while many newer pages use inline CSS; `assets/js/main.js` is currently not referenced by any page.
- `config/midtrans.php` and `config/wa_gateway.php` contain local credentials and are ignored by Git. Use the `.example.php` files when adding configuration.
