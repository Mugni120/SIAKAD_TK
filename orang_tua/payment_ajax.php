<?php
session_start();
if(!isset($_SESSION['level']) || $_SESSION['level'] != "Orang Tua"){
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
include '../config/koneksi.php';
include '../config/midtrans.php';

$id_tagihan = intval($_POST['id_tagihan']);

// Ambil data tagihan
$q = mysqli_query($koneksi, "SELECT t.*, s.nama_siswa, s.no_hp_ortu 
    FROM tagihan t 
    JOIN siswa s ON t.id_siswa = s.id_siswa 
    WHERE t.id_tagihan = '$id_tagihan' AND t.id_siswa = '{$_SESSION['id_siswa']}'");
$d = mysqli_fetch_assoc($q);

if(!$d) {
    echo json_encode(['success' => false, 'message' => 'Tagihan tidak ditemukan']);
    exit();
}

if($d['status'] == 'lunas') {
    echo json_encode(['success' => false, 'message' => 'Tagihan sudah lunas']);
    exit();
}

// Buat order ID unik
$order_id = 'SPP-' . $id_tagihan . '-' . time();

// Simpan transaksi
mysqli_query($koneksi, "INSERT INTO transaksi (id_tagihan, order_id, gross_amount) 
    VALUES ('$id_tagihan', '$order_id', '{$d['jumlah']}')");

// Mode simulasi - tanpa API key asli
if(MIDTRANS_SIMULATION) {
    // Simulasi berhasil - update tagihan jadi lunas
    mysqli_query($koneksi, "UPDATE transaksi SET 
        transaction_status = 'settlement',
        payment_type = 'bank_transfer',
        transaction_time = NOW(),
        settlement_time = NOW(),
        va_number = '8808' . rand(10000000, 99999999),
        bank = 'bca'
        WHERE order_id = '$order_id'");
    
    mysqli_query($koneksi, "UPDATE tagihan SET status = 'lunas' WHERE id_tagihan = '$id_tagihan'");
    
    echo json_encode([
        'success' => true, 
        'simulation' => true,
        'order_id' => $order_id,
        'message' => 'Pembayaran berhasil (simulasi)'
    ]);
    exit();
}

// Mode live - gunakan API Midtrans
$customer = [
    'name' => $d['nama_siswa'],
    'phone' => $d['no_hp_ortu'],
];

$result = midtrans_create_transaction($order_id, $d['jumlah'], $customer);

if($result['success']) {
    echo json_encode(['success' => true, 'token' => $result['token']]);
} else {
    // Tandai transaksi gagal agar tidak menggantung di status 'pending' selamanya
    mysqli_query($koneksi, "UPDATE transaksi SET transaction_status = 'failed', transaction_time = NOW() 
        WHERE order_id = '$order_id'");
    echo json_encode(['success' => false, 'message' => $result['message']]);
}
