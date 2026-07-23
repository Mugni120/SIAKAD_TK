<?php
session_start();
// Mematikan tampilan warning agar layout tidak berantakan jika data belum lengkap
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

if (!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua" || !isset($_SESSION['id_siswa'])) {
    header("location:index.php");
    exit();
}

include '../config/koneksi.php';
$id_siswa = $_SESSION['id_siswa'];

// Query untuk mengambil profil siswa dan kelas
$query_profil = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas, kelas.tahun_ajaran FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas WHERE siswa.id_siswa = '$id_siswa'");
$profil = mysqli_fetch_assoc($query_profil);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Orang Tua - SIAKAD</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #0f5132; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; margin: 0; }
        .navbar { background: white; padding: 15px 40px; display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .profile-bar { background: linear-gradient(135deg, var(--primary), #1e4620); color: white; border-radius: 16px; padding: 25px; margin-bottom: 25px; }
        .menu-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .menu-box { background: white; border-radius: 16px; padding: 30px; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn { padding: 12px 24px; border-radius: 24px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 14px; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-outline { border: 2px solid #2563eb; color: #2563eb; }
        .btn-nav { padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; }
        .btn-logout { background: #fee2e2; color: #dc3545; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div style="font-weight: 800; color: var(--primary);"><i class="bi bi-mortarboard-fill"></i> SIAKAD ORANG TUA</div>
        <div>
            <a href="index.php" class="btn-nav" style="background:#f1f5f9; color:#475569;">Ganti Anak</a>
            <a href="../logout.php" class="btn-nav btn-logout">Keluar</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-bar">
            <h2 style="margin:0 0 5px 0;"><?php echo $profil['nama_siswa'] ?? 'Data Siswa Belum Lengkap'; ?></h2>
            <p style="margin:0; opacity:0.9; font-size:14px;">
                Kelas: <?php echo $profil['nama_kelas'] ?? '-'; ?> | 
                TA: <?php echo $profil['tahun_ajaran'] ?? '-'; ?> | 
                NISN: <?php echo $profil['id_siswa'] ?? '-'; ?>
            </p>
        </div>

        <div style="background: white; border-radius: 16px; padding: 25px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 25px;">
            <h3 style="margin: 0 0 15px 0; color: #1e293b; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-megaphone-fill" style="color: #eab308;"></i> Pengumuman Sekolah Terbaru
            </h3>
            <?php
            // Mengambil 3 pengumuman terbaru
            $query_pengumuman_ortua = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id_pengumuman DESC LIMIT 3");
            if (mysqli_num_rows($query_pengumuman_ortua) > 0) {
                while ($p_ortua = mysqli_fetch_assoc($query_pengumuman_ortua)) {
            ?>
                    <div style="border-bottom: 1px solid #f1f5f9; padding: 15px 0; margin-bottom: 5px;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; margin-bottom: 6px;">
                            <h4 style="margin: 0; color: var(--primary); font-size: 15px; font-weight: 700;"><?php echo htmlspecialchars($p_ortua['judul']); ?></h4>
                            <small style="color: #64748b; font-size: 12px; white-space: nowrap; background: #f8fafc; padding: 2px 8px; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($p_ortua['tanggal_buat'])); ?>
                            </small>
                        </div>
                        <p style="margin: 0; color: #475569; font-size: 13px; line-height: 1.6;">
                            <?php echo nl2br(htmlspecialchars($p_ortua['isi_pengumuman'])); ?>
                        </p>
                    </div>
            <?php
                }
            } else {
                echo "<p style='color: #94a3b8; font-size: 13px; text-align: center; margin: 15px 0;'>Belum ada informasi terbaru dari sekolah untuk orang tua.</p>";
            }
            ?>
        </div>

        <div class="menu-grid">
            <div class="menu-box">
                <i class="bi bi-file-earmark-pdf" style="font-size: 40px; color: var(--primary);"></i>
                <h3>Laporan Perkembangan</h3>
                <p style="color:#64748b; font-size:13px; min-height: 40px;">Lihat rekap capaian nilai 6 aspek perkembangan anak didik.</p>
                <a href="cetak_laporan.php" class="btn btn-primary"><i class="bi bi-eye-fill"></i> Buka Laporan</a>
            </div>

            <div class="menu-box">
                <i class="bi bi-calendar2-check" style="font-size: 40px; color: #2563eb;"></i>
                <h3>Riwayat Absensi</h3>
                <p style="color:#64748b; font-size:13px; min-height: 40px;">Pantau data kehadiran, sakit, izin, dan tingkat ketidakhadiran anak.</p>
                <a href="absensi.php" class="btn btn-outline"><i class="bi bi-calendar-range"></i> Buka Absensi</a>
            </div>
        </div>
    </div>

</body>
</html>