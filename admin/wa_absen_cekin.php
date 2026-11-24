<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';
include '../config/wa_gateway.php';

$tanggal = date('Y-m-d');
$hasil = null;

// Pemicu manual lewat POST (boleh melewati jam cutoff)
if(isset($_POST['jalankan'])) {
    $hasil = wa_notif_belum_absen($tanggal, true);
}

// Preview: daftar siswa yang belum hadir hari ini, tanpa mengirim WA
$belum = wa_notif_belum_absen($tanggal, false);

$now = date('H:i');
$lewat_cutoff = ($now >= WA_CUTOFF_TIME);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Absensi QR - Admin SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link href="../assets/css/style.css?v=4" rel="stylesheet">
    <style>
        body { display: block !important; }
        .sidebar { position: static !important; width: auto !important; height: auto !important; background-color: #0f172a !important; }
        .sidebar .nav-link { color: rgba(255,255,255,.85) !important; white-space: nowrap; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #ffffff !important; background-color: rgba(13,148,136,.25) !important; border-radius: 6px; }
    </style>
    <link href="../assets/css/cards.css?v=13" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-none d-md-block bg-dark sidebar min-vh-100">
            <div class="sidebar-sticky pt-3">
                <h4 class="text-white text-center mb-4">SIAKAD TK</h4>
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link text-white" href="dashboard.php"> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="guru.php"> Data Guru</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="siswa.php"> Data Siswa</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="kelas.php"> Data Kelas</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="tagihan.php"> Tagihan SPP</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="clustering.php"> Clustering</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="qr_generator.php"> QR Generator</a></li>
                    <li class="nav-item"><a class="nav-link text-white active" href="wa_absen_cekin.php"> Cek Absensi QR</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="wa_logs.php"> WA Logs</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="pengumuman.php"> Pengumuman</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="../logout.php"> Keluar</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <h2 class="mb-3"><i class="bi bi-alarm"></i> Cek Siswa Belum Hadir (Notifikasi WA)</h2>

            <div class="card mb-4">
                <div class="card-body">
                    <p class="mb-1">
                        <strong>Tanggal:</strong> <?= date('d/m/Y', strtotime($tanggal)) ?> &nbsp;|&nbsp;
                        <strong>Jam sekarang:</strong> <?= $now ?> &nbsp;|&nbsp;
                        <strong>Batas (cutoff):</strong> <?= WA_CUTOFF_TIME ?>
                        <?= $lewat_cutoff
                            ? '<span class="badge bg-success ms-2">Sudah lewat cutoff</span>'
                            : '<span class="badge bg-warning text-dark ms-2">Belum lewat cutoff</span>' ?>
                    </p>
                    <p class="text-muted mb-0" style="font-size:.9rem;">
                        Siswa yang belum discan kartu QR-nya hingga pukul <?= WA_CUTOFF_TIME ?> akan dinotifikasi
                        lewat WhatsApp ke orang tuanya (maksimal 1x per hari). Pengiriman otomatis dijalankan oleh
                        Windows Task Scheduler via <code>cron/wa_notif_absen.php</code>;
                        tombol di bawah dipakai untuk memicu manual kapan saja.
                    </p>
                </div>
            </div>

            <?php if($hasil): ?>
            <div class="alert alert-<?= ($hasil['gagal'] > 0) ? 'warning' : 'success' ?>">
                <strong>Hasil pengiriman:</strong>
                <?= $hasil['total_belum'] ?> belum hadir —
                <?= $hasil['terkirim'] ?> terkirim,
                <?= $hasil['dilewati'] ?> dilewati (sudah dinotifikasi),
                <?= $hasil['gagal'] ?> gagal.
                <?php if(WA_ENABLED === false): ?>
                    <br><em>Mode simulasi aktif (WA_ENABLED = false) — pesan hanya tersimpan di WA Logs.</em>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-send"></i> Jalankan Pengecekan Sekarang
                </div>
                <div class="card-body">
                    <form method="POST">
                        <button type="submit" name="jalankan" value="1" class="btn btn-warning">
                            <i class="bi bi-hourglass-split"></i> Kirim Notifikasi "Belum Hadir" Sekarang
                        </button>
                        <span class="text-muted ms-3" style="font-size:.9rem;">
                            Anti-duplikat: siswa yang sudah dinotifikasi hari ini tidak akan dikirim ulang.
                        </span>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle"></i>
                    Siswa Belum Tercatat Hadir Hari Ini (<?= $belum['total_belum'] ?> siswa)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Status Notifikasi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $no = 1; foreach($belum['rincian'] as $r): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($r['nama']) ?></td>
                                    <td><?= htmlspecialchars($r['kelas'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($r['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if($belum['total_belum'] == 0): ?>
                                <tr><td colspan="4" class="text-center text-muted">
                                    Semua siswa sudah tercatat hadir ✅
                                </td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
