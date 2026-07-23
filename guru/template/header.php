<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAKAD Guru - TK Al-Manaarussa'diyyah</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="wrapper">
    <div class="sidebar" style="background-color: #1e293b;">
        <div class="brand" style="background-color: #0f172a;">SIAKAD GURU</div>
        <ul>
            <li><a href="dashboard.php"> Beranda Dashboard</a></li>
            <li><a href="absensi.php"> Absensi Harian</a></li>
            <li><a href="perkembangan.php"> Laporan Perkembangan</a></li>
            <li><a href="../logout.php" style="color: #f87171;"> Keluar Sistem</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="toggle-btn"> Ruang Pendidik TK Al-Manaarussa'diyyah</div>
            <div class="user-info">
                Guru: <b><?php echo $_SESSION['username']; ?></b> 
            </div>
        </div>
        
        <div class="container">