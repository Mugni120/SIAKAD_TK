<?php
session_start();
include '../config/koneksi.php';
$id_siswa = $_SESSION['id_siswa'];

$query_profil = mysqli_query($koneksi, "SELECT siswa.nama_siswa FROM siswa WHERE id_siswa = '$id_siswa'");
$profil = mysqli_fetch_assoc($query_profil);

$query_hitung = mysqli_query($koneksi, "
    SELECT 
        SUM(CASE WHEN status = 'Hadir' THEN 1 ELSE 0 END) as total_hadir,
        SUM(CASE WHEN status = 'Sakit' THEN 1 ELSE 0 END) as total_sakit,
        SUM(CASE WHEN status = 'Izin' THEN 1 ELSE 0 END) as total_izin,
        SUM(CASE WHEN status = 'Alpha' THEN 1 ELSE 0 END) as total_alfa
    FROM absensi 
    WHERE id_siswa = '$id_siswa'
");
$absensi = mysqli_fetch_assoc($query_hitung);

$query_riwayat = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_siswa = '$id_siswa' ORDER BY tanggal DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Absensi - SIAKAD</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; padding: 40px 20px; color: #1e293b; }
        .container { max-width: 900px; margin: auto; }
        
        /* Header & Tombol */
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-back { background: white; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 12px; text-decoration: none; color: #64748b; font-weight: 600; font-size: 14px; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .btn-back:hover { background: #f1f5f9; color: #0f5132; border-color: #cbd5e1; }

        /* Card Utama */
        .card { background: white; padding: 30px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .absensi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 25px 0; }
        .absensi-card { padding: 20px; border-radius: 16px; text-align: center; color: white; }
        .bg-hadir { background: #10b981; } .bg-sakit { background: #f59e0b; } 
        .bg-izin { background: #3b82f6; } .bg-alfa { background: #ef4444; }
        .counter { font-size: 32px; font-weight: 700; margin-top: 5px; }
        
        /* Tabel */
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 20px; }
        th { background: #f8fafc; padding: 15px; text-align: left; border-bottom: 2px solid #e2e8f0; font-size: 14px; }
        td { padding: 15px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #e2e8f0; color: #475569; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-section">
        <div>
            <h2 style="margin:0;">Riwayat Absensi</h2>
            <p style="color: #64748b; margin: 5px 0 0 0;">Ananda: <b><?php echo $profil['nama_siswa']; ?></b></p>
        </div>
        <a href="dashboard.php" class="btn-back"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
    </div>

    <div class="card">
        <div class="absensi-grid">
            <div class="absensi-card bg-hadir"><div>Hadir</div><div class="counter"><?php echo $absensi['total_hadir'] ?? 0; ?></div></div>
            <div class="absensi-card bg-sakit"><div>Sakit</div><div class="counter"><?php echo $absensi['total_sakit'] ?? 0; ?></div></div>
            <div class="absensi-card bg-izin"><div>Izin</div><div class="counter"><?php echo $absensi['total_izin'] ?? 0; ?></div></div>
            <div class="absensi-card bg-alfa"><div>Alpha</div><div class="counter"><?php echo $absensi['total_alfa'] ?? 0; ?></div></div>
        </div>

        <table>
            <thead><tr><th>Tanggal</th><th>Status</th><th>Catatan Guru</th></tr></thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($query_riwayat)){ ?>
                <tr>
                    <td style="font-weight:600;"><?php echo $row['tanggal']; ?></td>
                    <td><span class="badge"><?php echo $row['status']; ?></span></td>
                    <td style="color: #64748b;"><?php echo !empty($row['keterangan']) ? $row['keterangan'] : '-'; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>