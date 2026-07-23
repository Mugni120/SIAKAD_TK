<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD Admin - TK Al-Manaarussa'diyyah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <div class="wrapper">
        <div class="sidebar">
            <div class="brand">SIAKAD AL-MANAAR</div>
            <ul>
                <li><a href="dashboard.php"> Dashboard</a></li>
                <li><a href="guru.php"> Data Guru</a></li>
                <li><a href="siswa.php"> Data Siswa</a></li>
                <li><a href="kelas.php"> Data Kelas</a></li>
                <li><a href="pengumuman.php"> Pengumuman</a></li>
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