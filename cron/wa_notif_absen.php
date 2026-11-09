<?php
/**
 * Cek siswa yang belum hadir (belum discan QR) hingga jam cutoff
 * dan kirim notifikasi WhatsApp ke orang tuanya.
 *
 * Dijalankan otomatis via Windows Task Scheduler setiap hari (lihat catatan bawah),
 * atau manual lewat browser/tabel:
 *   C:\xampp\php\php.exe C:\xampp\htdocs\SIAKAD_TK\cron\wa_notif_absen.php
 *   C:\xampp\php\php.exe C:\xampp\htdocs\SIAKAD_TK\cron\wa_notif_absen.php 2026-11-09
 *
 * Aturan jam: hanya mengirim jika waktu sekarang sudah >= WA_CUTOFF_TIME
 * (lihat config/wa_gateway.php). Untuk menguji lebih awal, jalankan manual
 * via admin/wa_absen_cekin.php (tombol "Jalankan Sekarang").
 *
 * Setup Windows Task Scheduler (sekali saja):
 *   Program/script : C:\xampp\php\php.exe
 *   Arguments      : -f C:\xampp\htdocs\SIAKAD_TK\cron\wa_notif_absen.php
 *   Trigger        : Harian, mis. 08:05
 */

if (PHP_SAPI !== 'cli') {
    // Cegah eksekusi dari browser langsung
    http_response_code(403);
    exit("Script ini hanya bisa dijalankan via CLI (php.exe).\n");
}

require __DIR__ . '/../config/koneksi.php';
require __DIR__ . '/../config/wa_gateway.php';

$tanggal = isset($argv[1]) ? $argv[1] : date('Y-m-d');
$tanggal = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? $tanggal : date('Y-m-d');

$now = date('H:i');
echo "=== SIAKAD TK - Cek Siswa Belum Hadir ===\n";
echo "Tanggal : {$tanggal}\n";
echo "Waktu   : {$now}\n";
echo "Cutoff  : " . WA_CUTOFF_TIME . "\n";

if ($now < WA_CUTOFF_TIME) {
    echo "Belum melewati jam cutoff - dilewati, tidak ada yang dikirim.\n";
    exit(0);
}

if (!WA_ENABLED) {
    echo "WA disabled (mode simulasi) - no log 'absen_tidak_hadir' akan tercatat.\n";
}

$hasil = wa_notif_belum_absen($tanggal, true);

echo "Siswa belum hadir : {$hasil['total_belum']}\n";
echo "Terkirim          : {$hasil['terkirim']}\n";
echo "Dilewati (duplikat): {$hasil['dilewati']}\n";
echo "Gagal             : {$hasil['gagal']}\n";

foreach ($hasil['rincian'] as $r) {
    echo "- {$r['nama']} ({$r['kelas']}): {$r['status']}\n";
}
