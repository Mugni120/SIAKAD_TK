<?php
session_start();
// Pastikan hanya Guru yang bisa mengakses
if (!isset($_SESSION['level']) || $_SESSION['level'] != "Guru") {
    echo "<script>alert('Akses Ditolak!'); window.location='../index.php';</script>";
    exit();
}
include '../config/koneksi.php';

// Ambil ID dari session (mengantisipasi jika di proses_login menggunakan id_user atau id_guru)
$id_user = $_SESSION['id_user'] ?? 0;
$id_guru = $_SESSION['id_guru'] ?? 0;

// PERBAIKAN: Hubungkan data kelas dengan tabel guru yang sedang login secara dinamis
if (!empty($id_guru)) {
    // Jika session menyimpan id_guru langsung
    $query_kelas = mysqli_query($koneksi, "SELECT kelas.* FROM kelas INNER JOIN guru ON kelas.id_kelas = guru.id_kelas WHERE guru.id_guru = '$id_guru'");
} else {
    // Jika session menyimpan id_user, kita cari id_guru lewat tabel users
    $query_kelas = mysqli_query($koneksi, "SELECT kelas.* FROM kelas INNER JOIN guru ON kelas.id_kelas = guru.id_kelas INNER JOIN users ON guru.id_guru = users.id_guru WHERE users.id_user = '$id_user'");
}

$data_kelas = mysqli_fetch_assoc($query_kelas);
$id_kelas_aktif = $data_kelas['id_kelas'] ?? 0;
$nama_kelas_aktif = $data_kelas['nama_kelas'] ?? 'Belum Pilih Kelas';

// Hitung total siswa di kelas yang diampu guru aktif
$hitung_siswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM siswa WHERE id_kelas = '$id_kelas_aktif'");
$row_hitung = mysqli_fetch_assoc($hitung_siswa);
$total_siswa = $row_hitung['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Absensi Harian - SIAKAD</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-color: #0f5132;
            --bg-body: #f8f9fa;
            --card-border: #eef2f5;
            --text-main: #212529;
            --text-muted: #6c757d;

            /* Status Colors */
            --clr-hadir: #198754;
            --clr-izin: #0d6efd;
            --clr-sakit: #f59e0b;
            --clr-alpha: #dc3545;
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

        /* Top Header Action */
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

        .btn-back-dashboard {
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

        .btn-back-dashboard:hover {
            background-color: #f8fafc;
            color: #0f5132;
            border-color: #94a3b8;
        }

        .btn-class-pill {
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 10px 18px;
            border-radius: 24px;
            font-size: 14px;
            font-weight: 600;
            color: var(--text-main);
        }

        .btn-submit-entry {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 11px 22px;
            border-radius: 24px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-submit-entry:hover {
            opacity: 0.9;
        }

        /* 4 Summary Cards Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
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

        .stat-desc {
            font-size: 12px;
            color: var(--text-muted);
        }

        .card-total .stat-icon {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .card-hadir .stat-icon {
            background: #e3f2fd;
            color: #1565c0;
        }

        .card-izin .stat-icon {
            background: #fff3e0;
            color: #ef6c00;
        }

        .card-alpha .stat-icon {
            background: #ffebee;
            color: #c62828;
        }

        /* Main Table Container */
        .table-card {
            background: white;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .attendance-table th {
            background-color: #f8fafc;
            padding: 14px 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .attendance-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            font-size: 14px;
        }

        /* Student Profile Design */
        .profile-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #475569;
            font-size: 14px;
        }

        .profile-info .student-name {
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .profile-info .student-nisn {
            font-size: 11px;
            color: var(--text-muted);
            margin: 2px 0 0 0;
        }

        /* Custom Radio Button Grid */
        .radio-container {
            display: inline-block;
            position: relative;
            cursor: pointer;
            user-select: none;
        }

        .radio-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            border: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #94a3b8;
            transition: all 0.2s ease;
        }

        .radio-hadir input:checked~.checkmark-circle {
            background-color: #e8f5e9;
            border-color: var(--clr-hadir);
            color: var(--clr-hadir);
        }

        .radio-izin input:checked~.checkmark-circle {
            background-color: #e3f2fd;
            border-color: var(--clr-izin);
            color: var(--clr-izin);
        }

        .radio-sakit input:checked~.checkmark-circle {
            background-color: #fff3e0;
            border-color: var(--clr-sakit);
            color: var(--clr-sakit);
        }

        .radio-alpha input:checked~.checkmark-circle {
            background-color: #ffebee;
            border-color: var(--clr-alpha);
            color: var(--clr-alpha);
        }

        .input-note {
            width: 100%;
            max-width: 200px;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-note:focus {
            border-color: var(--primary-color);
        }

        .text-center {
            text-align: center !important;
        }
    </style>
</head>

<body>

    <div class="dashboard-container">
        <form action="absensi_aksi.php" method="POST">
            <input type="hidden" name="tanggal" value="<?php echo date('Y-m-d'); ?>">

            <div class="attendance-header">
                <div class="header-title">
                    <h1>Absensi Harian</h1>
                    <p>Pencatatan untuk Hari Ini, <?php echo date('d F Y'); ?></p>
                </div>

                <div class="header-actions">
                    <a href="dashboard.php" class="btn-back-dashboard">
                        <i class="bi bi-arrow-left-short" style="font-size: 18px;"></i> Dashboard
                    </a>
                    <a href="rekap_absensi.php" class="btn-back-dashboard" style="color: #0d6efd; border-color: #b6d4fe;">
                        <i class="bi bi-journal-text"></i> Rekap Data
                    </a>
                    <button type="button" class="btn-class-pill">Kelas <?php echo $nama_kelas_aktif; ?></button>
                    <button type="submit" class="btn-submit-entry">
                        <i class="bi bi-file-earmark-check"></i> Simpan Absensi
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card card-total">
                    <div class="stat-icon"><i class="bi bi-people"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Total Siswa</span>
                        <span class="stat-value"><?php echo $total_siswa; ?></span>
                        <span class="stat-desc">Kelas <?php echo $nama_kelas_aktif; ?></span>
                    </div>
                </div>
                <div class="stat-card card-hadir">
                    <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Hadir</span>
                        <span class="stat-value">-</span>
                        <span class="stat-desc">Belum disimpan</span>
                    </div>
                </div>
                <div class="stat-card card-izin">
                    <div class="stat-icon"><i class="bi bi-envelope"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Izin / Sakit</span>
                        <span class="stat-value">-</span>
                        <span class="stat-desc">Rekap harian</span>
                    </div>
                </div>
                <div class="stat-card card-alpha">
                    <div class="stat-icon"><i class="bi bi-x-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Alpha</span>
                        <span class="stat-value">-</span>
                        <span class="stat-desc">Tanpa keterangan</span>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th width="40%">Profil Peserta Didik</th>
                            <th width="10%" class="text-center">Hadir</th>
                            <th width="10%" class="text-center">Izin</th>
                            <th width="10%" class="text-center">Sakit</th>
                            <th width="10%" class="text-center">Alpha</th>
                            <th width="15%">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $query_siswa = mysqli_query($koneksi, "SELECT id_siswa, nama_siswa FROM siswa WHERE id_kelas = '$id_kelas_aktif' ORDER BY nama_siswa ASC");

                        if (mysqli_num_rows($query_siswa) == 0) {
                            echo "<tr><td colspan='7' class='text-center' style='color: var(--text-muted); padding: 30px;'>Belum ada data siswa di kelas ini.</td></tr>";
                        }

                        while ($siswa = mysqli_fetch_assoc($query_siswa)) {
                            $initial = strtoupper(substr($siswa['nama_siswa'], 0, 1));
                        ?>
                            <tr>
                                <td class="text-center" style="color: var(--text-muted); font-weight: 500;"><?php echo $no++; ?></td>
                                <td>
                                    <div class="profile-cell">
                                        <div class="avatar-circle"><?php echo $initial; ?></div>
                                        <div class="profile-info">
                                            <p class="student-name"><?php echo $siswa['nama_siswa']; ?></p>
                                            <p class="student-nisn">NIS: <?php echo str_pad($siswa['id_siswa'], 4, '0', STR_PAD_LEFT); ?></p>
                                        </div>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <label class="radio-container radio-hadir">
                                        <input type="radio" name="status[<?php echo $siswa['id_siswa']; ?>]" value="Hadir" checked>
                                        <span class="checkmark-circle"><i class="bi bi-check-lg"></i></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="radio-container radio-izin">
                                        <input type="radio" name="status[<?php echo $siswa['id_siswa']; ?>]" value="Izin">
                                        <span class="checkmark-circle"><i class="bi bi-envelope"></i></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="radio-container radio-sakit">
                                        <input type="radio" name="status[<?php echo $siswa['id_siswa']; ?>]" value="Sakit">
                                        <span class="checkmark-circle"><i class="bi bi-briefcase"></i></span>
                                    </label>
                                </td>
                                <td class="text-center">
                                    <label class="radio-container radio-alpha">
                                        <input type="radio" name="status[<?php echo $siswa['id_siswa']; ?>]" value="Alpha">
                                        <span class="checkmark-circle"><i class="bi bi-x-lg"></i></span>
                                    </label>
                                </td>

                                <td>
                                    <input type="text" name="keterangan[<?php echo $siswa['id_siswa']; ?>]" class="input-note" placeholder="Tambah catatan...">
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

</body>

</html>