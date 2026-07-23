<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Guru") {
    echo "<script>alert('Akses Ditolak!'); window.location='../index.php';</script>";
    exit();
}
include '../config/koneksi.php';

// Ambil id_guru dari session yang aktif saat login
// (Pastikan nama $_SESSION['id_guru'] sesuai dengan yang kamu set di proses login)
$id_guru = $_SESSION['id_guru'];

// Ambil data kelas yang terikat dengan guru yang sedang login
$query_kelas = mysqli_query($koneksi, "
    SELECT k.* FROM kelas k 
    JOIN guru g ON g.id_kelas = k.id_kelas 
    WHERE g.id_guru = '$id_guru'
");
$data_kelas = mysqli_fetch_assoc($query_kelas);

$id_kelas_aktif = $data_kelas['id_kelas'] ?? 0;
$nama_kelas_aktif = $data_kelas['nama_kelas'] ?? 'Belum Ditentukan';

// Set tanggal filter, default adalah hari ini
$tanggal_pilih = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Hitung total siswa di kelas ini
$hitung_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa WHERE id_kelas = '$id_kelas_aktif'");
$total_siswa = mysqli_fetch_assoc($hitung_siswa)['total'] ?? 0;

// Hitung rekapitulasi berdasarkan tanggal yang dipilih
$query_rekap = mysqli_query($koneksi, "
    SELECT 
        SUM(CASE WHEN a.status = 'Hadir' THEN 1 ELSE 0 END) as hadir,
        SUM(CASE WHEN a.status = 'Izin' THEN 1 ELSE 0 END) as izin,
        SUM(CASE WHEN a.status = 'Sakit' THEN 1 ELSE 0 END) as sakit,
        SUM(CASE WHEN a.status = 'Alpha' THEN 1 ELSE 0 END) as alpha
    FROM absensi a
    JOIN siswa s ON a.id_siswa = s.id_siswa
    WHERE a.tanggal = '$tanggal_pilih' AND s.id_kelas = '$id_kelas_aktif'
");
$rekap = mysqli_fetch_assoc($query_rekap);

$jml_hadir = $rekap['hadir'] ?? 0;
$jml_izin = $rekap['izin'] ?? 0;
$jml_sakit = $rekap['sakit'] ?? 0;
$jml_alpha = $rekap['alpha'] ?? 0;
$sudah_absen = $jml_hadir + $jml_izin + $jml_sakit + $jml_alpha;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Absensi Harian - SIAKAD</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #0f5132;
            --bg-body: #f8f9fa;
            --card-border: #eef2f5;
            --text-main: #212529;
            --text-muted: #6c757d;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 24px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .attendance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .header-title h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            color: #111827;
        }

        .header-title p {
            margin: 4px 0 0 0;
            color: var(--text-muted);
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* Buttons & Inputs */
        .btn-outline {
            background-color: #ffffff;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 10px 18px;
            border-radius: 24px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-outline:hover {
            background-color: #f8fafc;
            color: #0f5132;
            border-color: #94a3b8;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 24px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-filter {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            padding: 6px 12px;
            border-radius: 24px;
            border: 1px solid #cbd5e1;
        }

        .date-filter input[type="date"] {
            border: none;
            outline: none;
            font-family: inherit;
            font-size: 14px;
            color: #475569;
            font-weight: 500;
            cursor: pointer;
        }

        .date-filter button {
            background: none;
            border: none;
            color: var(--primary-color);
            font-weight: 600;
            cursor: pointer;
            padding: 4px 8px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .stat-value {
            font-size: 22px;
            font-weight: 700;
            margin: 2px 0;
        }

        .card-hadir .stat-icon {
            background: #e8f5e9;
            color: #198754;
        }

        .card-izin .stat-icon {
            background: #e3f2fd;
            color: #0d6efd;
        }

        .card-sakit .stat-icon {
            background: #fff3e0;
            color: #f59e0b;
        }

        .card-alpha .stat-icon {
            background: #ffebee;
            color: #dc3545;
        }

        /* Table */
        .table-card {
            background: white;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            overflow: hidden;
        }

        .rekap-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .rekap-table th {
            background-color: #f8fafc;
            padding: 14px 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .rekap-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-hadir {
            background: #e8f5e9;
            color: #198754;
        }

        .badge-izin {
            background: #e3f2fd;
            color: #0d6efd;
        }

        .badge-sakit {
            background: #fff3e0;
            color: #f59e0b;
        }

        .badge-alpha {
            background: #ffebee;
            color: #dc3545;
        }

        .badge-belum {
            background: #f1f5f9;
            color: #64748b;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <div class="attendance-header">
            <div class="header-title">
                <h1>Rekap Absensi Harian</h1>
                <p>Memantau kehadiran siswa <?php echo $nama_kelas_aktif; ?></p>
            </div>

            <div class="header-actions">
                <a href="dashboard.php" class="btn-outline">
                    <i class="bi bi-house"></i> Dashboard
                </a>
                <a href="absensi.php" class="btn-outline">
                    <i class="bi bi-pencil-square"></i> Input Absen
                </a>

                <form action="" method="GET" class="date-filter">
                    <i class="bi bi-calendar3" style="color: #94a3b8; margin-left: 8px;"></i>
                    <input type="date" name="tanggal" value="<?php echo $tanggal_pilih; ?>">
                    <button type="submit">Cari</button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card card-hadir">
                <div class="stat-icon"><i class="bi bi-check-lg"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Hadir</span>
                    <span class="stat-value"><?php echo $jml_hadir; ?></span>
                </div>
            </div>
            <div class="stat-card card-izin">
                <div class="stat-icon"><i class="bi bi-envelope"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Izin</span>
                    <span class="stat-value"><?php echo $jml_izin; ?></span>
                </div>
            </div>
            <div class="stat-card card-sakit">
                <div class="stat-icon"><i class="bi bi-briefcase"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Sakit</span>
                    <span class="stat-value"><?php echo $jml_sakit; ?></span>
                </div>
            </div>
            <div class="stat-card card-alpha">
                <div class="stat-icon"><i class="bi bi-x-lg"></i></div>
                <div class="stat-info">
                    <span class="stat-label">Alpha</span>
                    <span class="stat-value"><?php echo $jml_alpha; ?></span>
                </div>
            </div>
        </div>

        <div class="table-card">
            <table class="rekap-table">
                <thead>
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="15%">NISN</th>
                        <th width="35%">Nama Siswa</th>
                        <th width="20%" class="text-center">Status Kehadiran</th>
                        <th width="25%">Catatan Guru</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    // Query LEFT JOIN agar siswa yang belum diabsen tetap muncul
                    $query_data = mysqli_query($koneksi, "
                    SELECT s.id_siswa, s.nama_siswa, a.status, a.keterangan 
                    FROM siswa s 
                    LEFT JOIN absensi a ON s.id_siswa = a.id_siswa AND a.tanggal = '$tanggal_pilih'
                    WHERE s.id_kelas = '$id_kelas_aktif' 
                    ORDER BY s.nama_siswa ASC
                ");

                    if (mysqli_num_rows($query_data) == 0) {
                        echo "<tr><td colspan='5' class='text-center' style='padding: 30px; color: #64748b;'>Belum ada data siswa.</td></tr>";
                    }

                    while ($row = mysqli_fetch_assoc($query_data)) {
                        // Menentukan warna badge status
                        $status = $row['status'];
                        if ($status == 'Hadir') $badge_class = 'badge-hadir';
                        elseif ($status == 'Izin') $badge_class = 'badge-izin';
                        elseif ($status == 'Sakit') $badge_class = 'badge-sakit';
                        elseif ($status == 'Alpha') $badge_class = 'badge-alpha';
                        else {
                            $status = 'Belum Diabsen';
                            $badge_class = 'badge-belum';
                        }
                    ?>
                        <tr>
                            <td class="text-center" style="color: #64748b;"><?php echo $no++; ?></td>
                            <td style="color: #64748b; font-size: 13px;"><?php echo str_pad($row['id_siswa'], 8, '0', STR_PAD_LEFT); ?></td>
                            <td style="font-weight: 600; color: #1e293b;"><?php echo $row['nama_siswa']; ?></td>
                            <td class="text-center">
                                <span class="status-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                            </td>
                            <td style="color: #64748b; font-style: italic; font-size: 13px;">
                                <?php echo !empty($row['keterangan']) ? $row['keterangan'] : '-'; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>