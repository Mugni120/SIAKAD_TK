<?php
/**
 * Contoh konfigurasi Midtrans Payment Gateway
 *
 * Cara pakai:
 * 1. Salin file ini menjadi  config/midtrans.php  (tidak ikut ke git)
 * 2. Daftar di https://dashboard.midtrans.com (Sandbox dulu)
 * 3. Ambil Access Keys di https://dashboard.sandbox.midtrans.com/settings/access-keys
 * 4. Isi Server Key & Client Key di bawah ini
 */

define('MIDTRANS_SERVER_KEY', 'SB-Mid-server-GANTI_ANDA');
define('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-GANTI_ANDA');
define('MIDTRANS_SANDBOX', true); // true = sandbox (testing), false = production
define('MIDTRANS_SIMULATION', false); // true = simulasi (tanpa API key asli), false = live Midtrans

define('MIDTRANS_BASE_URL', MIDTRANS_SANDBOX
    ? 'https://api.sandbox.midtrans.com/v2'
    : 'https://api.midtrans.com/v2');

define('MIDTRANS_SNAP_URL', MIDTRANS_SANDBOX
    ? 'https://app.sandbox.midtrans.com/snap/snap.js'
    : 'https://app.midtrans.com/snap/snap.js');

define('MIDTRANS_SNAP_API_URL', MIDTRANS_SANDBOX
    ? 'https://app.sandbox.midtrans.com/snap/v1/transactions'
    : 'https://app.midtrans.com/snap/v1/transactions');

/**
 * Buat transaksi Snap baru
 * Kembalikan format: ['success' => bool, 'token' => string, 'redirect_url' => string]
 */
function midtrans_create_transaction($order_id, $gross_amount, $customer) {
    // customer_details: email hanya disertakan bila valid (Midtrans menolak format kosong/invalid)
    $customer_details = [
        'first_name' => $customer['name'],
        'phone'      => preg_replace('/[^0-9+]/', '', (string)($customer['phone'] ?? '')),
    ];
    $email = trim((string)($customer['email'] ?? ''));
    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $customer_details['email'] = $email;
    }

    $payload = [
        'transaction_details' => [
            'order_id'     => $order_id,
            'gross_amount' => (int)$gross_amount,
        ],
        'customer_details' => $customer_details,
        'enabled_payments' => ['bca_va', 'bni_va', 'bri_va', 'permata_va', 'gopay', 'qris'],
        'expiry' => [
            'duration' => 1,
            'unit'     => 'day',
        ],
    ];

    // Snap API: payload JSON -> mengembalikan token & redirect_url untuk snap.pay()
    $ch = curl_init(MIDTRANS_SNAP_API_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($http_code >= 200 && $http_code < 300 && isset($result['token'])) {
        return [
            'success'      => true,
            'token'        => $result['token'],
            'redirect_url' => $result['redirect_url'],
        ];
    }

    return [
        'success' => false,
        'message' => $result['status_message'] ?? ($result['error_messages'][0] ?? 'Gagal membuat transaksi (HTTP ' . $http_code . ')'),
    ];
}

/**
 * Cek status transaksi melalui Core API
 */
function midtrans_check_status($order_id) {
    $ch = curl_init(MIDTRANS_BASE_URL . '/' . $order_id . '/status');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode(MIDTRANS_SERVER_KEY . ':'),
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
