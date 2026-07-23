<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua" || !isset($_SESSION['id_siswa'])){
    header("location:index.php");
    exit();
}

include '../config/koneksi.php';
$id_siswa = $_SESSION['id_siswa'];
$semester_aktif = isset($_GET['semester']) ? mysqli_real_escape_string($koneksi, $_GET['semester']) : 'Ganjil';

// Ambil profil & nilai
$query_profil = mysqli_query($koneksi, "SELECT siswa.*, kelas.nama_kelas, kelas.tahun_ajaran FROM siswa JOIN kelas ON siswa.id_kelas = kelas.id_kelas WHERE siswa.id_siswa = '$id_siswa'");
$profil = mysqli_fetch_assoc($query_profil);

$query_nilai = mysqli_query($koneksi, "SELECT * FROM laporan_siswa WHERE id_siswa = '$id_siswa' AND semester = '$semester_aktif'");
$nilai = mysqli_fetch_assoc($query_nilai);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Rapor Resmi - SIAKAD</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; margin: 0; padding: 24px; color: #000; }
        .container { max-width: 850px; margin: 0 auto; }
        .no-print-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; background: white; padding: 15px 20px; border-radius: 8px; border:1px solid #e2e8f0; }
        .rapor-paper { background: white; border: 1px solid #000; padding: 50px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .table-rapor { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .table-rapor th, .table-rapor td { border: 1px solid #000; padding: 10px; font-size: 14px; }
        .table-rapor th { background: #f1f5f9; font-weight: 700; text-align: center; }
        .catatan-box { border: 1px solid #000; padding: 15px; margin-top: 20px; }
        .ttd-grid { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; text-align: center; font-size: 14px; row-gap: 40px; }
        .btn { padding: 10px 20px; border-radius: 24px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer; border: none; }
        
        @media print {
            body { background: white; padding: 0; }
            .no-print-header { display: none !important; }
            .rapor-paper { border: none; padding: 0; box-shadow: none; }
            .table-rapor th { background: #eaedf1 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="container">
    
    <div class="no-print-header">
        <a href="dashboard.php?semester=<?php echo $semester_aktif; ?>" class="btn" style="background:#64748b; color:white;"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
        
        <div style="display: flex; align-items: center; gap: 10px;">
            <label for="pilih_semester" style="font-size: 13px; font-weight: 600; color: #475569;">
                <i class="bi bi-folder2-open text-primary"></i> Pilih Semester:
            </label>
            <select id="pilih_semester" onchange="window.location.href='cetak_laporan.php?semester=' + this.value" style="padding: 8px 16px; border-radius: 24px; border: 1px solid #cbd5e1; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 13px; font-weight: 600; color: #334155; background-color: #f8fafc; cursor: pointer; outline: none;">
                <option value="Ganjil" <?php echo $semester_aktif == 'Ganjil' ? 'selected' : ''; ?>>Semester Ganjil</option>
                <option value="Genap" <?php echo $semester_aktif == 'Genap' ? 'selected' : ''; ?>>Semester Genap</option>
            </select>
        </div>

        <button onclick="window.print()" class="btn" style="background:#2563eb; color:white;"><i class="bi bi-printer"></i> Cetak Dokumen Rapor</button>
    </div>

    <div class="rapor-paper">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 18px; font-weight:700;">SIAKAD TK AL-MANAARUSSA'DIYYAH</h2>
            <p style="margin: 4px 0 0 0; font-size: 11px;">Alamat Komplek Pendidikan Islam Al-Manaarussa'diyyah, Jawa Barat</p>
            <div style="border-top: 2px solid #000; border-bottom: 1px solid #000; height: 2px; margin-top: 10px; margin-bottom: 20px;"></div>
            <h3 style="margin: 0; font-size: 14px; font-weight: 700; text-transform: uppercase;">LAPORAN CAPAIAN PERKEMBANGAN ANAK DIDIK</h3>
            <p style="margin: 3px 0 0 0; font-size: 13px;"><b>SEMESTER: <?php echo strtoupper($semester_aktif); ?></b></p>
        </div>

        <table style="width: 100%; font-size: 14px; margin-bottom: 20px;">
            <tr>
                <td width="18%" style="padding:4px 0;">Nama Siswa</td>
                <td width="2%">:</td>
                <td width="40%"><b><?php echo $profil['nama_siswa']; ?></b></td>
                <td width="18%">Kelas</td>
                <td width="2%">:</td>
                <td width="20%"><?php echo $profil['nama_kelas']; ?></td>
            </tr>
            <tr>
                <td style="padding:4px 0;">ID / NISN</td>
                <td>:</td>
                <td><?php echo $profil['id_siswa']; ?></td>
                <td>Tahun Ajaran</td>
                <td>:</td>
                <td><?php echo $profil['tahun_ajaran']; ?></td>
            </tr>
        </table>

        <?php if($nilai) { ?>
            <table class="table-rapor">
                <thead>
                    <tr>
                        <th width="8%">NO</th>
                        <th width="57%">ASPEK PERKEMBANGAN</th>
                        <th width="35%">SKALA CAPAIAN</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td style="text-align:center;">1</td><td><b>Kognitif</b></td><td style="text-align:center;"><b><?php echo $nilai['kognitif']; ?></b></td></tr>
                    <tr><td style="text-align:center;">2</td><td><b>Fisik Motorik</b></td><td style="text-align:center;"><b><?php echo $nilai['motorik']; ?></b></td></tr>
                    <tr><td style="text-align:center;">3</td><td><b>Sosial Emosional</b></td><td style="text-align:center;"><b><?php echo $nilai['sosial_emosional']; ?></b></td></tr>
                    <tr><td style="text-align:center;">4</td><td><b>Bahasa</b></td><td style="text-align:center;"><b><?php echo $nilai['bahasa']; ?></b></td></tr>
                    <tr><td style="text-align:center;">5</td><td><b>Seni & Kreativitas</b></td><td style="text-align:center;"><b><?php echo $nilai['seni']; ?></b></td></tr>
                    <tr><td style="text-align:center;">6</td><td><b>Nilai Agama & Moral</b></td><td style="text-align:center;"><b><?php echo $nilai['agama']; ?></b></td></tr>
                </tbody>
            </table>

            <div class="catatan-box">
                <b style="display:block; margin-bottom:5px; text-transform:uppercase; font-size:12px;">Catatan / Narasi Wali Kelas:</b>
                <p style="margin: 0; font-style: italic;">"<?php echo $nilai['catatan_guru']; ?>"</p>
            </div>

            <div class="ttd-grid">
                <div>
                    <div>Mengetahui,</div><div>Orang Tua / Wali Murid</div>
                    <div style="margin-top: 55px;">( ________________________ )</div>
                </div>
                <div>
                    <div>Bandung, ................................... 2026</div><div>Wali Kelas</div>
                    <div style="margin-top: 55px;">( ________________________ )</div>
                </div>
                <div style="grid-column: 1/-1;">
                    <div>Mengetahui,</div><div>Kepala Sekolah TK Al-Manaarussa'diyyah</div>
                    <div style="margin-top: 55px;"><b>( ________________________ )</b></div>
                </div>
            </div>
        <?php } else { ?>
            <div style="text-align: center; padding: 40px; border: 1px dashed #000; margin-top: 20px; background-color: #fffdec; border-radius: 8px;">
                <i class="bi bi-exclamation-triangle" style="font-size: 30px; color: #eab308; display: block; margin-bottom: 10px;"></i>
                <p style="margin: 0; font-weight: 600; color: #475569;">Data rapor perkembangan untuk Semester <?php echo htmlspecialchars($semester_aktif); ?> belum di-input oleh Wali Kelas.</p>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>