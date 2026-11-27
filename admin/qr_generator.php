<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

// Generate QR untuk semua siswa yang belum punya
$siswa = mysqli_query($koneksi, "SELECT s.id_siswa, s.nama_siswa FROM siswa s 
    LEFT JOIN siswa_qr q ON s.id_siswa = q.id_siswa 
    WHERE q.id_qr IS NULL");

while($s = mysqli_fetch_assoc($siswa)) {
    $kode = 'TK-' . md5($s['id_siswa'] . time() . rand(1000,9999));
    mysqli_query($koneksi, "INSERT INTO siswa_qr (id_siswa, kode_qr) VALUES ('{$s['id_siswa']}', '$kode')");
}

// Ambil semua QR
$q = mysqli_query($koneksi, "SELECT q.*, s.nama_siswa, k.nama_kelas 
    FROM siswa_qr q
    JOIN siswa s ON q.id_siswa = s.id_siswa
    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
    ORDER BY s.nama_siswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Generator - Admin SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link href="../assets/css/style.css?v=6" rel="stylesheet">
    <script src="../assets/js/qrcodejs.min.js"></script>
    <style>
        .qr-card img { max-width: 150px; height: auto; }
        @media print {
            .no-print { display: none !important; }
            .qr-card { break-inside: avoid; border: 1px solid #000; }
        }
    </style>

    <style>
        /* Netralisir aturan .sidebar/body dari style.css agar grid Bootstrap berfungsi normal */
        body { display: block !important; }
        .sidebar {
            position: static !important;
            width: auto !important;
            height: auto !important;
            background-color: #0f172a !important;
        }
        .sidebar .nav-link { color: rgba(255,255,255,.85) !important; white-space: nowrap; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #ffffff !important; background-color: rgba(13,148,136,.25) !important; border-radius: 6px; }
    </style>
    <link href="../assets/css/cards.css?v=14" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-none d-md-block bg-dark sidebar min-vh-100 class='no-print'">
                <div class="sidebar-sticky pt-3">
                    <h4 class="text-white text-center mb-4">SIAKAD TK</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link text-white" href="dashboard.php"> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="guru.php"> Data Guru</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="siswa.php"> Data Siswa</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="kelas.php"> Data Kelas</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="tagihan.php"> Tagihan SPP</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="clustering.php"> Clustering</a></li>
                        <li class="nav-item"><a class="nav-link text-white active" href="qr_generator.php"> QR Generator</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="wa_logs.php"> WA Logs</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="pengumuman.php"> Pengumuman</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../logout.php"> Keluar</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                    <h2><i class="bi bi-qr-code"></i> QR Code Kartu Pelajar</h2>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Cetak Semua
                    </button>
                </div>

                <div class="row">
                    <?php while($d = mysqli_fetch_assoc($q)): ?>
                    <div class="col-md-4 col-lg-3 mb-4">
                        <div class="qr-card h-100 d-flex flex-column align-items-center justify-content-center">
                            <h5 class="text-primary"><?= htmlspecialchars($d['nama_siswa']) ?></h5>
                            <p class="text-muted mb-2"><?= htmlspecialchars($d['nama_kelas']) ?></p>
                            <div id="qr-<?= $d['id_qr'] ?>"></div>
                            <p class="mt-2 mb-0"><small class="text-muted"><?= $d['kode_qr'] ?></small></p>
                        </div>
                    </div>
                    <script>
                        new QRCode(document.getElementById('qr-<?= $d['id_qr'] ?>'), {
                            text: '<?= $d['kode_qr'] ?>',
                            width: 150,
                            height: 150
                        });
                    </script>
                    <?php endwhile; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
