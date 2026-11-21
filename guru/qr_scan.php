<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Guru"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';
include '../config/wa_gateway.php';

$tanggal = date('Y-m-d');
$id_guru = isset($_SESSION['id_guru']) ? $_SESSION['id_guru'] : 0;

// Proses scan QR
if(isset($_POST['kode_qr'])) {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode_qr']);
    
    // Cari siswa berdasarkan kode QR
    $q = mysqli_query($koneksi, "SELECT s.id_siswa, s.nama_siswa, s.no_hp_ortu, k.nama_kelas 
        FROM siswa_qr q
        JOIN siswa s ON q.id_siswa = s.id_siswa
        LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
        WHERE q.kode_qr = '$kode'");
    
    if(mysqli_num_rows($q) > 0) {
        $d = mysqli_fetch_assoc($q);
        $id_siswa = $d['id_siswa'];
        
        // Cek apakah sudah absen hari ini
        $cek = mysqli_query($koneksi, "SELECT id_absensi FROM absensi WHERE id_siswa='$id_siswa' AND tanggal='$tanggal'");
        
        if(mysqli_num_rows($cek) > 0) {
            $pesan = "Siswa {$d['nama_siswa']} sudah tercatat hadir hari ini.";
            $status = 'warning';
        } else {
            // Insert absensi
            mysqli_query($koneksi, "INSERT INTO absensi (id_siswa, id_guru, tanggal, status, keterangan) 
                VALUES ('$id_siswa', '$id_guru', '$tanggal', 'Hadir', 'Absensi via QR Code')");
            
            // Kirim notifikasi WA
            wa_notif_absensi($id_siswa, 'Hadir', $tanggal, 'Absensi via QR Code');
            
            $pesan = "{$d['nama_siswa']} berhasil dicatat HADIR!";
            $status = 'success';
        }
    } else {
        $pesan = "QR Code tidak valid!";
        $status = 'danger';
    }

    header('Location: qr_scan.php?pesan=' . urlencode($pesan) . '&status=' . urlencode($status));
    exit();
}
?>

<?php
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR Absensi - Guru SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { background: #f8f9fa; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        #reader { width: 100%; max-width: 500px; margin: 0 auto; }
        .result-box { 
            padding: 20px; 
            border-radius: 15px; 
            text-align: center; 
            margin-top: 20px;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/cards.css?v=9">
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <span class="navbar-text text-white">
                <i class="bi bi-qr-code-scan"></i> Scan QR Absensi
            </span>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h5><i class="bi bi-camera"></i> Scan QR Code Kartu Pelajar</h5>
                    </div>
                    <div class="card-body">
                        <div id="reader"></div>
                        
                        <?php if($pesan !== null): ?>
                        <div class="result-box alert alert-<?= $status ?> alert-dismissible fade show">
                            <h5><?= $pesan ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Input Manual -->
                        <div class="mt-4 text-center">
                            <p class="text-muted">Atau masukkan kode secara manual:</p>
                            <form method="POST" class="d-flex justify-content-center gap-2">
                                <input type="text" name="kode_qr" class="form-control" style="max-width: 300px;" 
                                       placeholder="Masukkan kode QR" required>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Proses
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Daftar Siswa Sudah Absen Hari Ini -->
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-check2-all"></i> Sudah Absen Hari Ini (<?= date('d/m/Y') ?>)
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $q_absen = mysqli_query($koneksi, "SELECT a.*, s.nama_siswa, k.nama_kelas 
                                    FROM absensi a
                                    JOIN siswa s ON a.id_siswa = s.id_siswa
                                    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas
                                    WHERE a.tanggal = '$tanggal' AND a.id_guru = '$id_guru'
                                    ORDER BY a.id_absensi DESC");
                                $no = 1;
                                while($a = mysqli_fetch_assoc($q_absen)):
                                ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><?= htmlspecialchars($a['nama_siswa']) ?></td>
                                        <td><?= htmlspecialchars($a['nama_kelas']) ?></td>
                                        <td><span class="badge bg-success"><?= $a['status'] ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if(mysqli_num_rows($q_absen) == 0): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada yang absen</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function onScanSuccess(decodedText, decodedResult) {
        // Kirim ke server
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="kode_qr" value="' + decodedText + '">';
        document.body.appendChild(form);
        form.submit();
    }

    function onScanFailure(error) {
        // console.warn(`Code scan error = ${error}`);
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader",
        { fps: 10, qrbox: { width: 250, height: 250 } },
        false
    );
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>
