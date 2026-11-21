<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua"){
    header("location:../login.php?pesan=belum_login");
    exit();
}
include '../config/koneksi.php';

$id_siswa = $_SESSION['id_siswa'];

// Ambil data siswa
$q_siswa = mysqli_query($koneksi, "SELECT s.nama_siswa, k.nama_kelas FROM siswa s 
    LEFT JOIN kelas k ON s.id_kelas = k.id_kelas WHERE s.id_siswa = '$id_siswa'");
$d_siswa = mysqli_fetch_assoc($q_siswa);

// Ambil riwayat pembayaran - hanya transaksi yang valid
$q = mysqli_query($koneksi, "SELECT tr.*, t.bulan, t.tahun, t.jumlah, t.status as status_tagihan
    FROM transaksi tr
    JOIN tagihan t ON tr.id_tagihan = t.id_tagihan
    WHERE t.id_siswa = '$id_siswa' AND tr.transaction_status IS NOT NULL AND tr.transaction_status != 'failed'
    ORDER BY tr.created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pembayaran - SIAKAD TK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .status-lunas { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-gagal { background: #f8d7da; color: #721c24; }
        .payment-card { 
            background: white; 
            border-radius: 12px; 
            padding: 20px; 
            margin-bottom: 15px;
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }
        .payment-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .payment-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .info-item {
            display: flex;
            flex-direction: column;
        }
        .info-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        .empty-state i {
            font-size: 60px;
            color: #cbd5e1;
            margin-bottom: 20px;
        }
    </style>
    <link rel="stylesheet" href="../assets/css/cards.css?v=10">
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <span class="navbar-text text-white">
                <i class="bi bi-clock-history"></i> Riwayat Pembayaran
            </span>
        </div>
    </nav>

    <div class="container">
        <!-- Info Siswa -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($d_siswa['nama_siswa']) ?>
                </h5>
                <p class="card-text text-muted">Kelas: <?= htmlspecialchars($d_siswa['nama_kelas']) ?></p>
            </div>
        </div>

        <!-- Riwayat Pembayaran -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-clock-history"></i> Riwayat Pembayaran SPP
            </div>
            <div class="card-body">
                <?php 
                $total_lunas = 0;
                $total_pending = 0;
                $ada_data = false;
                
                while($d = mysqli_fetch_assoc($q)): 
                    $ada_data = true;
                    $status = $d['transaction_status'];
                    $status_class = '';
                    $status_text = '';
                    
                    // Tentukan status berdasarkan transaction_status DAN tagihan_status
                    if($status == 'settlement' || $status == 'capture') {
                        $status_class = 'status-lunas';
                        $status_text = 'Lunas';
                        $total_lunas += $d['gross_amount'];
                    } elseif($status == 'pending') {
                        $status_class = 'status-pending';
                        $status_text = 'Pending';
                        $total_pending += $d['gross_amount'];
                    } elseif($status == 'cancel') {
                        $status_class = 'status-gagal';
                        $status_text = 'Dibatalkan';
                    } elseif($status == 'expire') {
                        $status_class = 'status-gagal';
                        $status_text = 'Kedaluwarsa';
                    } elseif($status == 'deny') {
                        $status_class = 'status-gagal';
                        $status_text = 'Ditolak';
                    } else {
                        $status_class = 'status-pending';
                        $status_text = ucfirst($status);
                    }
                ?>
                <div class="payment-card">
                    <div class="payment-header">
                        <div>
                            <h5 class="mb-1"><?= $d['bulan'] ?> <?= $d['tahun'] ?></h5>
                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></small>
                        </div>
                        <span class="status-badge <?= $status_class ?>"><?= $status_text ?></span>
                    </div>
                    <div class="payment-info">
                        <div class="info-item">
                            <span class="info-label">Jumlah</span>
                            <span class="info-value">Rp <?= number_format($d['gross_amount'], 0, ',', '.') ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Metode</span>
                            <span class="info-value"><?= $d['payment_type'] ? ucfirst(str_replace('_', ' ', $d['payment_type'])) : 'Bank Transfer' ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Order ID</span>
                            <span class="info-value"><small><?= $d['order_id'] ?></small></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">VA Number</span>
                            <span class="info-value"><?= $d['va_number'] ?? '-' ?></span>
                        </div>
                        <?php if($d['bank']): ?>
                        <div class="info-item">
                            <span class="info-label">Bank</span>
                            <span class="info-value"><?= strtoupper($d['bank']) ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if($d['settlement_time']): ?>
                        <div class="info-item">
                            <span class="info-label">Waktu Lunas</span>
                            <span class="info-value"><?= date('d/m/Y H:i', strtotime($d['settlement_time'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
                
                <?php if(!$ada_data): ?>
                <div class="empty-state">
                    <i class="bi bi-receipt"></i>
                    <h5>Belum Ada Riwayat Pembayaran</h5>
                    <p>Anda belum melakukan pembayaran SPP apapun.</p>
                    <a href="tagihan.php" class="btn btn-primary">
                        <i class="bi bi-credit-card"></i> Bayar SPP Sekarang
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary -->
        <?php if($ada_data): ?>
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>Total Lunas</h6>
                        <h4>Rp <?= number_format($total_lunas, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h6>Total Pending</h6>
                        <h4>Rp <?= number_format($total_pending, 0, ',', '.') ?></h4>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
