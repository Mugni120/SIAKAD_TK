<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';
include '../config/midtrans.php';

$id_siswa = $_SESSION['id_siswa'];
$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($koneksi, $_GET['order_id']) : '';

// Ambil data transaksi - hanya milik anak orang tua yang login
$q = mysqli_query($koneksi, "SELECT tr.*, t.id_tagihan, t.bulan, t.tahun, s.nama_siswa 
    FROM transaksi tr
    JOIN tagihan t ON tr.id_tagihan = t.id_tagihan
    JOIN siswa s ON t.id_siswa = s.id_siswa
    WHERE tr.order_id = '$order_id' AND t.id_siswa = '$id_siswa'");
$d = mysqli_fetch_assoc($q);

// ===== Rekonsiliasi status dengan Midtrans =====
// Karena localhost tidak menerima notifikasi HTTP POST dari server Midtrans
// (payment_callback.php tidak tercapai dari luar), status disegel di sini:
// tanya status terkini langsung ke API Midtrans (server-to-server).
if ($d && $d['transaction_status'] !== 'settlement') {
    $st = midtrans_check_status($order_id);
    $status_midtrans = $st['transaction_status'] ?? '';

    if ($status_midtrans === 'settlement' || $status_midtrans === 'capture') {
        $payment_type = mysqli_real_escape_string($koneksi, $st['payment_type'] ?? '');
        $va = $st['va_numbers'][0]['va_number'] ?? '';
        $bank = $st['va_numbers'][0]['bank'] ?? '';
        $pdf_url = mysqli_real_escape_string($koneksi, $st['pdf_url'] ?? '');
        $ttime = mysqli_real_escape_string($koneksi, $st['transaction_time'] ?? '');
        $stime = mysqli_real_escape_string($koneksi, $st['settlement_time'] ?? $ttime);
        $id_siswa_esc = mysqli_real_escape_string($koneksi, $id_siswa);

        mysqli_query($koneksi, "UPDATE transaksi SET 
            transaction_status = 'settlement',
            payment_type = '$payment_type',
            transaction_time = '$ttime',
            settlement_time = '$stime',
            va_number = '$va',
            bank = '$bank',
            pdf_url = '$pdf_url'
            WHERE order_id = '$order_id'");
        mysqli_query($koneksi, "UPDATE tagihan SET status = 'lunas' 
            WHERE id_tagihan = '$d[id_tagihan]'");

        // Muat ulang status terkini untuk ditampilkan
        $q = mysqli_query($koneksi, "SELECT tr.*, t.id_tagihan, t.bulan, t.tahun, s.nama_siswa 
            FROM transaksi tr
            JOIN tagihan t ON tr.id_tagihan = t.id_tagihan
            JOIN siswa s ON t.id_siswa = s.id_siswa
            WHERE tr.order_id = '$order_id' AND t.id_siswa = '$id_siswa'");
        $d = mysqli_fetch_assoc($q);
    } elseif ($status_midtrans === 'pending') {
        mysqli_query($koneksi, "UPDATE transaksi SET transaction_status = 'pending',
            transaction_time = NOW() WHERE order_id = '$order_id'");
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Berhasil - SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; }
        .success-card { 
            max-width: 500px; 
            margin: 50px auto; 
            padding: 40px; 
            border-radius: 20px;
            text-align: center;
        }
        .check-icon { 
            font-size: 80px; 
            color: #28a745; 
            animation: scaleIn 0.5s ease;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/cards.css?v=8">
</head>
<body>
    <div class="container">
        <div class="card success-card">
            <div class="check-icon mb-3">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="text-success mb-3">Pembayaran Berhasil!</h2>
            <?php if($d): ?>
            <div class="alert alert-light border">
                <p><strong>Siswa:</strong> <?= htmlspecialchars($d['nama_siswa']) ?></p>
                <p><strong>Periode:</strong> <?= $d['bulan'] ?> <?= $d['tahun'] ?></p>
                <p><strong>Jumlah:</strong> Rp <?= number_format($d['gross_amount'], 0, ',', '.') ?></p>
                <p><strong>Order ID:</strong> <?= $d['order_id'] ?></p>
                <p><strong>Metode:</strong> <?= ucfirst($d['payment_type'] ?? 'N/A') ?></p>
            </div>
            <?php endif; ?>
            <a href="tagihan.php" class="btn btn-primary">
                <i class="bi bi-arrow-left"></i> Kembali ke Tagihan
            </a>
        </div>
    </div>
</body>
</html>
