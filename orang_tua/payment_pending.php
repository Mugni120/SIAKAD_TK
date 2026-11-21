<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

$order_id = isset($_GET['order_id']) ? mysqli_real_escape_string($koneksi, $_GET['order_id']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Pending - SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; }
        .pending-card { max-width: 500px; margin: 50px auto; padding: 40px; border-radius: 20px; text-align: center; }
        .pending-icon { font-size: 80px; color: #ffc107; }
    </style>
    <link rel="stylesheet" href="../assets/css/cards.css?v=9">
</head>
<body>
    <div class="container">
        <div class="card pending-card">
            <div class="pending-icon mb-3">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <h2 class="text-warning mb-3">Pembayaran Pending</h2>
            <p>Pembayaran Anda sedang diproses. Selesaikan pembayaran sesuai instruksi yang diberikan.</p>
            <p><strong>Order ID:</strong> <?= htmlspecialchars($order_id) ?></p>
            <a href="tagihan.php" class="btn btn-warning text-white mt-3">
                <i class="bi bi-arrow-left"></i> Kembali ke Tagihan
            </a>
        </div>
    </div>
</body>
</html>
