# SIAKAD TK

## Local setup

- This is a framework-free PHP/MySQL app intended for XAMPP. Serve this directory through Apache/MySQL and open `http://localhost/siakad_tk/` (the repository path is already under `C:\xampp\htdocs`).
- There is no Composer/npm manifest, build step, automated test suite, lint configuration, or CI. For a repo-wide PHP syntax check in this Windows setup, run:
  ```powershell
  Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & 'C:\xampp\php\php.exe' -l $_.FullName }
  ```
  Check one file with `& 'C:\xampp\php\php.exe' -l .\admin\dashboard.php`.
- Several pages depend on CDN-hosted Bootstrap, Bootstrap Icons, and Google Fonts; there is no local vendor bundle.

## Database

- `config/koneksi.php` hard-codes MySQL `localhost`, user `root`, an empty password, and database `data_tk`; there is no environment-based configuration. Keep the database name in sync with the SQL dump.
- `database/data_tk.sql` creates the `data_tk` schema and the tables used by the app (`users`, `kelas`, `siswa`, `guru`, `absensi`, `laporan_siswa`, and `pengumuman`), but contains no seed data and there is no migration system. Import it before first use with `Get-Content -Raw .\database\data_tk.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u root`; it does not drop existing tables.
- `buat_admin.php` is an exposed development bootstrap page: opening it creates or updates the `Admin` account `mugniAja` with password `mugni123`. Do not use or leave it exposed in a shared deployment.

## Application flow

- `index.php` is the landing page; `login.php` posts to `proses_login.php`, which uses `password_verify` and routes on the exact session role values `Admin`, `Guru`, and `Orang Tua`.
- `admin/`, `guru/`, and `orang_tua/` contain direct PHP page/action scripts rather than a framework or router. Admin CRUD pages use `?page=list|tambah|edit` and `*_aksi.php`; attendance/development actions are POST handlers. A parent first selects a child in `orang_tua/index.php`, which stores `$_SESSION['id_siswa']`.
- Login session data is role-specific: Guru sessions also need `id_guru` and `id_kelas`; parent sessions need `id_siswa`. Keep these names synchronized with the page queries.

## Change-sensitive quirks

- Authentication is implemented independently in each script; templates do not enforce access. New protected pages/actions must explicitly start the session and check the required role. Existing guard gaps are `admin/pengumuman.php`, `admin/pengumuman_edit.php`, and `orang_tua/absensi.php`—do not copy those patterns.
- Attendance persistence uses the `keterangan[...]` form-field name and the `absensi.keterangan` database column; keep those names aligned if changing the form or action.
- `admin/template/` and `guru/template/` are HTML partials, not shared controllers. `assets/css/style.css` is linked by those templates, while many newer pages use inline CSS; `assets/js/main.js` is currently not referenced by any page.
