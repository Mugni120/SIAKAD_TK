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
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
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
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/cards.css?v=11">
</head>
<body>

    <nav class="navbar no-print">
        <div style="font-weight: 800; color: var(--primary);"><i class="bi bi-mortarboard-fill"></i> SIAKAD ORANG TUA</div>
        <div>
            <a href="../logout.php" class="btn-nav btn-logout">Keluar</a>
        </div>
    </nav>

    <div class="container">
        <div class="profile-bar no-print">
            <h2 style="margin:0 0 5px 0;"><?php echo $profil['nama_siswa'] ?? 'Data Siswa Belum Lengkap'; ?></h2>
            <p style="margin:0; opacity:0.9; font-size:14px;">
                Kelas: <?php echo $profil['nama_kelas'] ?? '-'; ?> | 
                TA: <?php echo $profil['tahun_ajaran'] ?? '-'; ?> | 
                NISN: <?php echo $profil['id_siswa'] ?? '-'; ?>
            </p>
        </div>

        <div class="card info-card announcement-card no-print">
            <div class="info-card-heading">
                <span class="info-icon"><i class="bi bi-megaphone-fill"></i></span>
                <div>
                    <h3>Pengumuman Sekolah Terbaru</h3>
                    <p>Informasi terbaru dari sekolah untuk orang tua / wali murid.</p>
                </div>
            </div>
            <?php
            // Mengambil 3 pengumuman terbaru
            $query_pengumuman_ortua = mysqli_query($koneksi, "SELECT * FROM pengumuman ORDER BY id_pengumuman DESC LIMIT 3");
            if (mysqli_num_rows($query_pengumuman_ortua) > 0) {
                while ($p_ortua = mysqli_fetch_assoc($query_pengumuman_ortua)) {
            ?>
                    <div class="announcement-item">
                        <div class="announcement-head">
                            <h4><?php echo htmlspecialchars($p_ortua['judul']); ?></h4>
                            <span class="announcement-date">
                                <i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($p_ortua['tanggal_buat'])); ?>
                            </span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($p_ortua['isi_pengumuman'])); ?></p>
                    </div>
            <?php
                }
            } else {
                echo "<p class='announcement-empty'>Belum ada informasi terbaru dari sekolah untuk orang tua.</p>";
            }
            ?>
        </div>

        <?php
        // Kartu QR siswa untuk dicetak orang tua
        $q_qr = mysqli_query($koneksi, "SELECT q.*, k.nama_kelas FROM siswa_qr q LEFT JOIN siswa s ON q.id_siswa = s.id_siswa LEFT JOIN kelas k ON s.id_kelas = k.id_kelas WHERE q.id_siswa = '$id_siswa'");
        $d_qr = mysqli_fetch_assoc($q_qr);
        if ($d_qr):
        ?>
        <div class="card qr-card no-print">
            <h3>
                <i class="bi bi-qr-code"></i> QR Code Kartu Pelajar
            </h3>
            <p class="qr-card-desc">Tunjukkan saat penjemputan / scan untuk absensi.</p>
            <div id="qr-ortu" class="qr-code-box"></div>
            <p class="qr-student-name"><?php echo htmlspecialchars($profil['nama_siswa'] ?? '-'); ?></p>
            <p class="qr-student-class"><?php echo htmlspecialchars($d_qr['nama_kelas'] ?? '-'); ?> &bull; <small><?php echo $d_qr['kode_qr']; ?></small></p>
            <div class="no-print">
                <button onclick="window.print()" class="btn btn-primary" style="border:none; cursor:pointer;"><i class="bi bi-printer"></i> Cetak QR Code</button>
            </div>
            <script src="../assets/js/qrcodejs.min.js"></script>
            <script>
                new QRCode(document.getElementById('qr-ortu'), {
                    text: '<?php echo $d_qr['kode_qr']; ?>',
                    width: 160,
                    height: 160
                });
            </script>
        </div>
        <?php endif; ?>

        <div class="menu-grid no-print">
            <div class="menu-box">
                <div class="menu-visual"><i class="bi bi-file-earmark-pdf"></i></div>
                <h3>Laporan Perkembangan</h3>
                <p style="color:#64748b; font-size:13px; min-height: 40px;">Lihat rekap capaian nilai 6 aspek perkembangan anak didik.</p>
                <a href="cetak_laporan.php" class="btn btn-primary"><i class="bi bi-eye-fill"></i> Buka Laporan</a>
            </div>

            <div class="menu-box">
                <div class="menu-visual" style="background: linear-gradient(135deg, #eff6ff, #dbeafe); color: #2563eb;"><i class="bi bi-calendar2-check"></i></div>
                <h3>Riwayat Absensi</h3>
                <p style="color:#64748b; font-size:13px; min-height: 40px;">Pantau data kehadiran, sakit, izin, dan tingkat ketidakhadiran anak.</p>
                <a href="absensi.php" class="btn btn-outline"><i class="bi bi-calendar-range"></i> Buka Absensi</a>
            </div>

            <div class="menu-box">
                <div class="menu-visual" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #059669;"><i class="bi bi-cash-stack"></i></div>
                <h3>Tagihan SPP</h3>
                <p style="color:#64748b; font-size:13px; min-height: 40px;">Lihat dan bayar tagihan SPP bulanan anak secara online.</p>
                <a href="tagihan.php" class="btn btn-primary"><i class="bi bi-credit-card"></i> Bayar SPP</a>
            </div>

            <div class="menu-box">
                <div class="menu-visual" style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); color: #7c3aed;"><i class="bi bi-clock-history"></i></div>
                <h3>Riwayat Pembayaran</h3>
                <p style="color:#64748b; font-size:13px; min-height: 40px;">Pantau riwayat pembayaran SPP yang telah dilakukan.</p>
                <a href="riwayat_pembayaran.php" class="btn btn-outline"><i class="bi bi-receipt"></i> Lihat Riwayat</a>
            </div>
        </div>
    </div>

</body>
</html>