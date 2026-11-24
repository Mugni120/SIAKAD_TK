<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD Admin - TK Al-Manaarussa'diyyah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=4">
    <link rel="stylesheet" href="../assets/css/cards.css?v=13">
</head>

<body>

    <div class="wrapper">
        <div class="sidebar">
            <div class="brand">SIAKAD TK</div>
            <ul>
                <?php $halaman = basename($_SERVER['PHP_SELF']); ?>
                <li class="<?= $halaman == 'dashboard.php' ? 'active' : '' ?>"><a href="dashboard.php"> Dashboard</a></li>
                <li class="<?= $halaman == 'guru.php' ? 'active' : '' ?>"><a href="guru.php"> Data Guru</a></li>
                <li class="<?= $halaman == 'siswa.php' ? 'active' : '' ?>"><a href="siswa.php"> Data Siswa</a></li>
                <li class="<?= $halaman == 'kelas.php' ? 'active' : '' ?>"><a href="kelas.php"> Data Kelas</a></li>
                <li class="<?= $halaman == 'tagihan.php' ? 'active' : '' ?>"><a href="tagihan.php"> Tagihan SPP</a></li>
                <li class="<?= $halaman == 'clustering.php' ? 'active' : '' ?>"><a href="clustering.php"> Clustering</a></li>
                <li class="<?= $halaman == 'qr_generator.php' ? 'active' : '' ?>"><a href="qr_generator.php"> QR Generator</a></li>
                <li class="<?= $halaman == 'wa_logs.php' ? 'active' : '' ?>"><a href="wa_logs.php"> WA Logs</a></li>
                <li class="<?= $halaman == 'pengumuman.php' ? 'active' : '' ?>"><a href="pengumuman.php"> Pengumuman</a></li>
                <li><a href="../logout.php" style="color: #e74c3c;"> Keluar</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="topbar">
                <div class="toggle-btn"> Sistem Informasi Akademik</div>
                <div class="user-info">
                    Halo, <b><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></b>
                </div>
            </div>

            <div class="container">