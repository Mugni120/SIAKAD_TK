# SIAKAD TK

SIAKAD TK adalah aplikasi manajemen akademik untuk Taman Kanak-Kanak. Aplikasi ini dibangun sebagai aplikasi PHP/MySQL legacy yang dibungkus oleh **Laravel 12** agar tetap bisa dijalankan melalui struktur proyek Laravel, tanpa mengubah alur aplikasi lama.

## Ringkasan

Aplikasi ini mendukung tiga jenis pengguna:

- **Admin**: mengelola data guru, siswa, kelas, tagihan SPP, pengumuman, QR code, clustering, dan log WhatsApp.
- **Guru**: mencatat absensi harian, input perkembangan siswa, scan QR, dan melihat rekap absensi.
- **Orang Tua**: melihat tagihan, riwayat pembayaran, laporan perkembangan anak, dan absensi anak.

Fitur utama:

- Login berbasis role (`Admin`, `Guru`, `Orang Tua`)
- Absensi harian dan scan QR
- Laporan perkembangan 6 aspek (kognitif, motorik, sosial-emosional, bahasa, seni, agama)
- Pembayaran SPP via Midtrans
- Notifikasi WhatsApp via Fonnte
- Clustering K-Means untuk pengelompokan perkembangan siswa
- Pembuatan QR code kartu pelajar
- Desain card dan tampilan responsif

## Teknologi

- PHP 8.2+
- MySQL
- Laravel 12 sebagai wrapper
- Bootstrap 5
- Bootstrap Icons
- JavaScript (html5-qrcode, Midtrans Snap)

## Struktur Penting

- `app/Http/Controllers/LegacyController.php` — controller wrapper untuk menjalankan file PHP legacy.
- `routes/legacy.php` — routing legacy.
- `config/koneksi.php` — koneksi MySQL legacy.
- `admin/` — halaman dan aksi admin.
- `guru/` — halaman dan aksi guru.
- `orang_tua/` — halaman dan aksi orang tua.
- `assets/css/` — stylesheet, termasuk `cards.css` dan `style.css`.
- `database/data_tk.sql` — skema database.
- `database/seed_data.sql` — data contoh.

## Cara Menjalankan

### 1. Prasyarat

- XAMPP Apache dan MySQL aktif.
- PHP tersedia melalui XAMPP.
- Composer terpasang.

### 2. Import Database

```powershell
Get-Content -Raw .\database\data_tk.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u root
```

Opsional, import data contoh:

```powershell
Get-Content -Raw .\database\seed_data.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u root
```

### 3. Install Dependency

```powershell
& 'C:\xampp\php\php.exe' C:\path\to\composer.phar install
```

### 4. Konfigurasi Environment

Jika `.env` belum ada:

```powershell
Copy-Item .env.example .env
& 'C:\xampp\php\php.exe' artisan key:generate
```

### 5. Jalankan Aplikasi

Cara standar Laravel:

```powershell
php artisan serve
```

atau:

```powershell
composer run dev
```

Lalu buka `http://127.0.0.1:8000/`.

Catatan:
- Apache tidak wajib dijalankan.
- MySQL tetap harus aktif karena aplikasi legacy menggunakan `config/koneksi.php`.
- Jika ingin port lain, gunakan `php artisan serve --port=8099`.

Melalui XAMPP Apache (opsional):

1. Nyalakan Apache dan MySQL.
2. Buka `http://localhost/siakad_tk/`.

## Database

Aplikasi legacy menggunakan `config/koneksi.php` dengan konfigurasi:

- Host: `localhost`
- User: `root`
- Password: kosong
- Database: `data_tk`

Pastikan database `data_tk` sudah di-import sebelum digunakan.

## Akun

Akun contoh lain tersedia pada `database/seed_data.sql`, dengan password yang disesuaikan pada data seed.

## Catatan Penting

- Laravel hanya berfungsi sebagai wrapper dan routing. Alur aplikasi tetap berada di file PHP legacy.
- `config/koneksi.php` tetap digunakan oleh aplikasi legacy, bukan konfigurasi database Laravel.
- Beberapa fitur opsional membutuhkan konfigurasi tambahan:
  - WhatsApp: `config/wa_gateway.php`
  - Midtrans: `config/midtrans.php`
- File konfigurasi dengan kredensial asli diabaikan oleh Git. Gunakan file `.example.php` sebagai acuan.
- Beberapa halaman masih memiliki celah guard role yang perlu diperhatikan pada deployment.

## Pengujian

Cek sintaks PHP:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & 'C:\xampp\php\php.exe' -l $_.FullName }
```

Cek Laravel:

```powershell
& 'C:\xampp\php\php.exe' artisan about
```

## Lisensi

Proyek ini menggunakan Laravel framework yang dilisensikan di bawah MIT License. Kode aplikasi SIAKAD TK mengikuti ketentuan repository pemilik.
