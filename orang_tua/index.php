<?php
session_start();
// Proteksi halaman orang tua
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua"){
    header("location:../login.php?pesan=belum_login");
    exit();
}

include '../config/koneksi.php';

// Proses jika orang tua memilih anak
// Saat link "Ganti Anak" diklik, sesi id_siswa dihapus
if(isset($_GET['reset'])) {
    unset($_SESSION['id_siswa']);
    header("location:index.php");
    exit();
}

// Jika ada parameter id_siswa di URL, simpan ke sesi lalu pindah ke dashboard
if(isset($_GET['id_siswa'])) {
    $_SESSION['id_siswa'] = $_GET['id_siswa'];
    header("location:dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Anak - SIAKAD TK AL-MANAARUSSA'DIYYAH</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #0f5132; --bg-body: #f4f6f9; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-body); margin: 0; padding: 0; }
        .navbar { background: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border-bottom: 1px solid #e2e8f0; }
        .container { max-width: 900px; margin: 50px auto; padding: 0 20px; }
        .welcome-box { text-align: center; margin-bottom: 40px; }
        .grid-anak { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; justify-content: center; }
        .card-anak { background: white; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.01); }
        .avatar { width: 70px; height: 70px; border-radius: 50%; background: #e8f5e9; color: var(--primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto; font-size: 28px; font-weight: 700; }
        .btn { padding: 10px 20px; border-radius: 24px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; background: var(--primary); color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div style="font-weight: 700; color: var(--primary);"><i class="bi bi-mortarboard-fill"></i> SIAKAD TK AL-MANAARUSSA'DIYYAH</div>
        <a href="../logout.php" style="color: #dc3545; text-decoration:none; font-size:14px; font-weight:600;"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </nav>

    <div class="container">
        <div class="welcome-box">
            <h1>Selamat Datang, Wali Murid</h1>
            <p style="color: #64748b;">Silakan pilih profil anak Anda untuk melihat laporan perkembangan.</p>
        </div>

        <div class="grid-anak">
            <?php 
            $query_anak = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas ORDER BY nama_siswa ASC");
            while($anak = mysqli_fetch_assoc($query_anak)) {
            ?>
                <div class="card-anak">
                    <div class="avatar"><?php echo strtoupper(substr($anak['nama_siswa'], 0, 1)); ?></div>
                    <h3 style="margin: 0 0 5px 0; font-size:16px;"><?php echo $anak['nama_siswa']; ?></h3>
                    <p style="margin: 0 0 15px 0; font-size: 13px; color: #64748b;">Kelas: <b><?php echo $anak['nama_kelas']; ?></b></p>
                    <a href="index.php?id_siswa=<?php echo $anak['id_siswa']; ?>" class="btn">
                        <i class="bi bi-person-check"></i> Pilih Anak
                    </a>
                </div>
            <?php } ?>
        </div>
    </div>

</body>
</html>