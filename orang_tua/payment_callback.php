<?php
/**
 * Midtrans Callback Handler
 * 
 * Midtrans akan mengirim POST request ke URL ini setiap ada perubahan status transaksi.
 * URL ini harus didaftarkan di Midtrans Dashboard -> Settings -> Configuration
 * 
 * Sandbox testing: https://dashboard.sandbox.midtrans.com/settings/config_info
 */

include '../config/koneksi.php';
include '../config/midtrans.php';

// Ambil notification dari Midtrans
$json = file_get_contents('php://input');
$notification = json_decode($json, true);

if(!$notification) {
    http_response_code(400);
    exit('Invalid JSON');
}

$order_id = $notification['order_id'] ?? '';
$transaction_status = $notification['transaction_status'] ?? '';
$payment_type = $notification['payment_type'] ?? '';
$gross_amount = $notification['gross_amount'] ?? 0;

// Verifikasi signature (opsional tapi direkomendasikan)
$server_key = MIDTRANS_SERVER_KEY;
$signature_key = hash('sha512', $order_id . $notification['status_code'] . $gross_amount . $server_key);
if($signature_key !== ($notification['signature_key'] ?? '')) {
    http_response_code(401);
    exit('Invalid signature');
}

// Update status transaksi
if($transaction_status == 'capture' || $transaction_status == 'settlement') {
    // Pembayaran berhasil
    mysqli_query($koneksi, "UPDATE transaksi SET 
        transaction_status = 'settlement',
        payment_type = '$payment_type',
        transaction_time = NOW(),
        settlement_time = NOW(),
        va_number = '" . ($notification['va_numbers'][0]['va_number'] ?? '') . "',
        bank = '" . ($notification['va_numbers'][0]['bank'] ?? '') . "',
        pdf_url = '" . ($notification['pdf_url'] ?? '') . "'
        WHERE order_id = '$order_id'");
    
    // Update tagihan jadi lunas
    mysqli_query($koneksi, "UPDATE tagihan t 
        JOIN transaksi tr ON t.id_tagihan = tr.id_tagihan 
        SET t.status = 'lunas' 
        WHERE tr.order_id = '$order_id'");
    
} elseif($transaction_status == 'pending') {
    mysqli_query($koneksi, "UPDATE transaksi SET 
        transaction_status = 'pending',
        payment_type = '$payment_type',
        transaction_time = NOW()
        WHERE order_id = '$order_id'");
        
} elseif($transaction_status == 'cancel' || $transaction_status == 'expire' || $transaction_status == 'deny') {
    mysqli_query($koneksi, "UPDATE transaksi SET 
        transaction_status = '$transaction_status',
        transaction_time = NOW()
        WHERE order_id = '$order_id'");
}

// Return 200 OK ke Midtrans
http_response_code(200);
echo json_encode(['status' => 'OK']);
