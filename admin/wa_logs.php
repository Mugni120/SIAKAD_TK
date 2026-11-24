<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Admin"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

$q = mysqli_query($koneksi, "SELECT * FROM wa_logs ORDER BY waktu_kirim DESC LIMIT 100");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Logs - Admin SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link href="../assets/css/style.css?v=4" rel="stylesheet">

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
                        <li class="nav-item"><a class="nav-link text-white active" href="wa_logs.php"> WA Logs</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="pengumuman.php"> Pengumuman</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="../logout.php"> Keluar</a></li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <h2 class="mb-4"><i class="bi bi-chat-dots"></i> WhatsApp Message Logs</h2>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No</th>
                                        <th>Waktu</th>
                                        <th>Tipe</th>
                                        <th>Nomor Tujuan</th>
                                        <th>Pesan</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                $no = 1;
                                while($d = mysqli_fetch_assoc($q)): 
                                    $status_class = [
                                        'success' => 'success',
                                        'simulated' => 'info',
                                        'failed' => 'danger',
                                        'pending' => 'warning'
                                    ][$d['status']] ?? 'secondary';
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($d['waktu_kirim'])) ?></td>
                                        <td><span class="badge bg-secondary"><?= $d['tipe'] ?: '-' ?></span></td>
                                        <td><?= htmlspecialchars($d['nomor_tujuan']) ?></td>
                                        <td><small><?= nl2br(htmlspecialchars(substr($d['pesan'], 0, 100))) ?>...</small></td>
                                        <td><span class="badge bg-<?= $status_class ?>"><?= ucfirst($d['status']) ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if(mysqli_num_rows($q) == 0): ?>
                                    <tr><td colspan="6" class="text-center">Belum ada log pesan</td></tr>
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
