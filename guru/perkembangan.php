<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Guru"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

// Ambil data kelas dari session guru yang login
$id_kelas = $_SESSION['id_kelas'];
$semester_aktif = isset($_GET['semester']) ? mysqli_real_escape_string($koneksi, $_GET['semester']) : 'Ganjil';

// Ambil data kelas untuk judul
$query_kelas = mysqli_query($koneksi, "SELECT * FROM kelas WHERE id_kelas='$id_kelas'");
$data_kelas  = mysqli_fetch_assoc($query_kelas);
$nama_kelas  = $data_kelas ? $data_kelas['nama_kelas'] : 'Belum Ditentukan';

// Tangkap ID Siswa jika ada
$id_siswa = isset($_GET['id_siswa']) ? mysqli_real_escape_string($koneksi, $_GET['id_siswa']) : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perkembangan - SIAKAD</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-color: #0f5132;
            --bg-body: #f8f9fa;
            --card-border: #eef2f5;
            --text-main: #111827;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 24px;
        }

        .container { max-width: 1000px; margin: 0 auto; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .page-header h1 { margin: 0; font-size: 26px; font-weight: 700; }
        .page-header p { margin: 4px 0 0 0; color: var(--text-muted); font-size: 14px; }
        
        .btn-secondary-outline {
            background: #fff;
            border: 1px solid #cbd5e1;
            padding: 10px 18px;
            border-radius: 24px;
            font-weight: 600;
            font-size: 13px;
            color: #475569;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Style untuk View 1: Daftar Siswa */
        .student-list-card {
            background: white;
            border: 1px solid var(--card-border);
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .student-table th {
            background: #f8fafc;
            color: #475569;
            text-align: left;
            padding: 14px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
        }
        .student-table td {
            padding: 14px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .btn-evaluate {
            background: var(--primary-color);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Style untuk View 2: Form Perkembangan */
        .student-profile-card {
            background: white;
            border: 1px solid var(--card-border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .profile-left { display: flex; align-items: center; gap: 16px; }
        .avatar-box {
            width: 56px; height: 56px; border-radius: 50%;
            background: #e8f5e9; color: var(--primary-color);
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 700;
        }
        .student-details h2 { margin: 0; font-size: 18px; font-weight: 700; }
        .badge-class {
            background: #e2e8f0; color: #475569; font-size: 11px;
            padding: 3px 8px; border-radius: 12px; margin-left: 6px;
        }
        .assessment-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 24px;
        }
        .assessment-card {
            background: white; border: 1px solid var(--card-border);
            border-radius: 14px; padding: 20px;
        }
        .card-icon-title { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
        .icon-wrap {
            width: 36px; height: 36px; border-radius: 8px; background: #f1f5f9;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .title-wrap h3 { margin: 0; font-size: 15px; font-weight: 700; }
        .form-group label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; margin-bottom: 6px; }
        .form-select { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; }
        .finalize-box { background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; gap: 16px; }
        .textarea-narasi { width: 100%; height: 100px; padding: 12px; border: 1px solid #a5d6a7; border-radius: 10px; resize: vertical; outline: none; font-family: inherit; }
        .btn-submit { background: var(--primary-color); color: white; border: none; padding: 12px 24px; border-radius: 24px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;}
        .text-center { text-align: center !important; }
    </style>
</head>
<body>

<div class="container">

    <?php 
    // ==========================================
    // VIEW 1: JIKA BELUM MEMILIH SISWA
    // ==========================================
    if(empty($id_siswa)) { 
        $query_daftar = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_kelas='$id_kelas' ORDER BY nama_siswa ASC");
    ?>
        <div class="page-header">
            <div class="header-text">
                <h1>Laporan Perkembangan</h1>
                <p>Pilih peserta didik di <b>Kelas <?php echo $nama_kelas; ?></b> untuk melakukan input evaluasi capaian.</p>
            </div>
            <a href="dashboard.php" class="btn-secondary-outline">
                <i class="bi bi-house"></i> Dashboard
            </a>
        </div>

        <div class="student-list-card">
            <table class="student-table">
                <thead>
                    <tr>
                        <th width="8%" class="text-center">No</th>
                        <th>Nama Lengkap Anak Didik</th>
                        <th width="25%" class="text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    if(mysqli_num_rows($query_daftar) > 0) {
                        while($row = mysqli_fetch_assoc($query_daftar)) {
                    ?>
                        <tr>
                            <td class="text-center" style="color: var(--text-muted); font-weight: 500;"><?php echo $no++; ?></td>
                            <td><strong><?php echo $row['nama_siswa']; ?></strong></td>
                            <td class="text-center">
                                <!-- Mengarah ke file yang sama tapi membawa parameter id_siswa -->
                                <a href="perkembangan.php?id_siswa=<?php echo $row['id_siswa']; ?>&semester=<?php echo $semester_aktif; ?>" class="btn-evaluate">
                                    <i class="bi bi-pencil-square"></i> Mulai Nilai
                                </a>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                        echo "<tr><td colspan='3' class='text-center' style='padding:30px; color:var(--text-muted);'>Belum ada data murid di kelas ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    <?php 
    // ==========================================
    // VIEW 2: JIKA SUDAH MEMILIH SISWA (FORM INPUT)
    // ==========================================
    } else { 
        // Ambil profil data anak berdasarkan ID
        $query_siswa = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas, kelas.tahun_ajaran FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas WHERE siswa.id_siswa = '$id_siswa'");
        $siswa = mysqli_fetch_assoc($query_siswa);

        // Ambil data nilai jika sudah pernah diisi
        $query_nilai = mysqli_query($koneksi, "SELECT * FROM laporan_siswa WHERE id_siswa = '$id_siswa' AND semester = '$semester_aktif'");
        $nilai = mysqli_fetch_assoc($query_nilai);
    ?>
        <form action="perkembangan_aksi.php" method="POST">
            <input type="hidden" name="id_siswa" value="<?php echo $id_siswa; ?>">
            <input type="hidden" name="semester" value="<?php echo $semester_aktif; ?>">

            <div class="page-header">
                <div class="header-text">
                    <h1>Input Assessment</h1>
                    <p>Update perkembangan periodik capaian siswa didik secara berkala.</p>
                </div>
                <!-- Tombol kembali mengarah ke peninjauan list siswa perkembangan.php (Tanpa parameter ID) -->
                <a href="perkembangan.php" class="btn-secondary-outline">
                    <i class="bi bi-arrow-left"></i> Ganti Siswa
                </a>
            </div>

            <div class="student-profile-card">
                <div class="profile-left">
                    <div class="avatar-box"><?php echo strtoupper(substr($siswa['nama_siswa'], 0, 1)); ?></div>
                    <div class="student-details">
                        <h2><?php echo $siswa['nama_siswa']; ?> <span class="badge-class"><?php echo $siswa['nama_kelas']; ?></span></h2>
                        <p>Periode Penilaian: <b>Semester <?php echo $semester_aktif; ?> (<?php echo $siswa['tahun_ajaran']; ?>)</b>.</p>
                    </div>
                </div>
            </div>

            <div class="assessment-grid">
                <!-- KOGNITIF -->
                <div class="assessment-card">
                    <div class="card-icon-title">
                        <div class="icon-wrap"><i class="bi bi-brain"></i></div>
                        <div class="title-wrap"><h3>Kognitif</h3></div>
                    </div>
                    <div class="form-group">
                        <label>Assessment Score</label>
                        <select name="kognitif" class="form-select">
                            <option value="BB" <?php echo ($nilai['kognitif'] ?? '') == 'BB' ? 'selected' : ''; ?>>Belum Berkembang (BB)</option>
                            <option value="MB" <?php echo ($nilai['kognitif'] ?? '') == 'MB' ? 'selected' : ''; ?>>Mulai Berkembang (MB)</option>
                            <option value="BSH" <?php echo ($nilai['kognitif'] ?? '') == 'BSH' ? 'selected' : 'selected'; ?>>Berkembang Sesuai Harapan (BSH)</option>
                            <option value="BSB" <?php echo ($nilai['kognitif'] ?? '') == 'BSB' ? 'selected' : ''; ?>>Berkembang Sangat Baik (BSB)</option>
                        </select>
                    </div>
                </div>

                <!-- FISIK MOTORIK -->
                <div class="assessment-card">
                    <div class="card-icon-title">
                        <div class="icon-wrap"><i class="bi bi-activity"></i></div>
                        <div class="title-wrap"><h3>Fisik Motorik</h3></div>
                    </div>
                    <div class="form-group">
                        <label>Assessment Score</label>
                        <select name="motorik" class="form-select">
                            <option value="BB" <?php echo ($nilai['motorik'] ?? '') == 'BB' ? 'selected' : ''; ?>>Belum Berkembang (BB)</option>
                            <option value="MB" <?php echo ($nilai['motorik'] ?? '') == 'MB' ? 'selected' : ''; ?>>Mulai Berkembang (MB)</option>
                            <option value="BSH" <?php echo ($nilai['motorik'] ?? '') == 'BSH' ? 'selected' : 'selected'; ?>>Berkembang Sesuai Harapan (BSH)</option>
                            <option value="BSB" <?php echo ($nilai['motorik'] ?? '') == 'BSB' ? 'selected' : ''; ?>>Berkembang Sangat Baik (BSB)</option>
                        </select>
                    </div>
                </div>

                <!-- SOSIAL EMOSIONAL -->
                <div class="assessment-card">
                    <div class="card-icon-title">
                        <div class="icon-wrap"><i class="bi bi-people"></i></div>
                        <div class="title-wrap"><h3>Sosial Emosional</h3></div>
                    </div>
                    <div class="form-group">
                        <label>Assessment Score</label>
                        <select name="sosial_emosional" class="form-select">
                            <option value="BB" <?php echo ($nilai['sosial_emosional'] ?? '') == 'BB' ? 'selected' : ''; ?>>Belum Berkembang (BB)</option>
                            <option value="MB" <?php echo ($nilai['sosial_emosional'] ?? '') == 'MB' ? 'selected' : ''; ?>>Mulai Berkembang (MB)</option>
                            <option value="BSH" <?php echo ($nilai['sosial_emosional'] ?? '') == 'BSH' ? 'selected' : 'selected'; ?>>Berkembang Sesuai Harapan (BSH)</option>
                            <option value="BSB" <?php echo ($nilai['sosial_emosional'] ?? '') == 'BSB' ? 'selected' : ''; ?>>Berkembang Sangat Baik (BSB)</option>
                        </select>
                    </div>
                </div>

                <!-- BAHASA -->
                <div class="assessment-card">
                    <div class="card-icon-title">
                        <div class="icon-wrap"><i class="bi bi-translate"></i></div>
                        <div class="title-wrap"><h3>Bahasa</h3></div>
                    </div>
                    <div class="form-group">
                        <label>Assessment Score</label>
                        <select name="bahasa" class="form-select">
                            <option value="BB" <?php echo ($nilai['bahasa'] ?? '') == 'BB' ? 'selected' : ''; ?>>Belum Berkembang (BB)</option>
                            <option value="MB" <?php echo ($nilai['bahasa'] ?? '') == 'MB' ? 'selected' : ''; ?>>Mulai Berkembang (MB)</option>
                            <option value="BSH" <?php echo ($nilai['bahasa'] ?? '') == 'BSH' ? 'selected' : 'selected'; ?>>Berkembang Sesuai Harapan (BSH)</option>
                            <option value="BSB" <?php echo ($nilai['bahasa'] ?? '') == 'BSB' ? 'selected' : ''; ?>>Berkembang Sangat Baik (BSB)</option>
                        </select>
                    </div>
                </div>

                <!-- SENI -->
                <div class="assessment-card">
                    <div class="card-icon-title">
                        <div class="icon-wrap"><i class="bi bi-palette"></i></div>
                        <div class="title-wrap"><h3>Seni & Kreativitas</h3></div>
                    </div>
                    <div class="form-group">
                        <label>Assessment Score</label>
                        <select name="seni" class="form-select">
                            <option value="BB" <?php echo ($nilai['seni'] ?? '') == 'BB' ? 'selected' : ''; ?>>Belum Berkembang (BB)</option>
                            <option value="MB" <?php echo ($nilai['seni'] ?? '') == 'MB' ? 'selected' : ''; ?>>Mulai Berkembang (MB)</option>
                            <option value="BSH" <?php echo ($nilai['seni'] ?? '') == 'BSH' ? 'selected' : 'selected'; ?>>Berkembang Sesuai Harapan (BSH)</option>
                            <option value="BSB" <?php echo ($nilai['seni'] ?? '') == 'BSB' ? 'selected' : ''; ?>>Berkembang Sangat Baik (BSB)</option>
                        </select>
                    </div>
                </div>

                <!-- AGAMA -->
                <div class="assessment-card">
                    <div class="card-icon-title">
                        <div class="icon-wrap"><i class="bi bi-book"></i></div>
                        <div class="title-wrap"><h3>Nilai Agama & Moral</h3></div>
                    </div>
                    <div class="form-group">
                        <label>Assessment Score</label>
                        <select name="agama" class="form-select">
                            <option value="BB" <?php echo ($nilai['agama'] ?? '') == 'BB' ? 'selected' : ''; ?>>Belum Berkembang (BB)</option>
                            <option value="MB" <?php echo ($nilai['agama'] ?? '') == 'MB' ? 'selected' : ''; ?>>Mulai Berkembang (MB)</option>
                            <option value="BSH" <?php echo ($nilai['agama'] ?? '') == 'BSH' ? 'selected' : 'selected'; ?>>Berkembang Sesuai Harapan (BSH)</option>
                            <option value="BSB" <?php echo ($nilai['agama'] ?? '') == 'BSB' ? 'selected' : ''; ?>>Berkembang Sangat Baik (BSB)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="finalize-box">
                <div class="finalize-header">
                    <h2>Finalisasi Laporan Evaluasi</h2>
                    <p>Tulis kesimpulan narasi deskripsi guru mengenai perkembangan menyeluruh anak didik.</p>
                </div>
                <textarea name="catatan_guru" class="textarea-narasi" placeholder="Ketik deskripsi narasi catatan guru di sini..." required><?php echo $nilai['catatan_guru'] ?? ''; ?></textarea>
                <div class="finalize-actions" style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn-submit"><i class="bi bi-send-check"></i> Submit Laporan Perkembangan</button>
                </div>
            </div>
        </form>
    <?php } ?>

</div>

</body>
</html>