<?php
require_once 'koneksi.php';
require_once 'vendor/autoload.php';

// Set konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-5loh9cbN5XG4J80gx1nby1ml';
\Midtrans\Config::$isProduction = false;

$notification_body = file_get_contents('php://input');
$notification = json_decode($notification_body, true);

// Ambil order_id dari notification
$order_id = $notification['order_id'];

// Verifikasi signature
$expectedSignature = hash('sha512', $order_id . $notification['status_code'] . $notification['gross_amount'] . \Midtrans\Config::$serverKey);
if ($notification['signature_key'] !== $expectedSignature) {
    die('Invalid signature');
}

// Update status pesanan berdasarkan notifikasi
$status = $notification['transaction_status'];
$fraud_status = $notification['fraud_status'];

if ($status == 'capture') {
    if ($fraud_status == 'challenge') {
        $order_status = 'pending';
    } else if ($fraud_status == 'accept') {
        $order_status = 'paid';
    }
} else if ($status == 'settlement') {
    $order_status = 'paid';
} else if ($status == 'cancel' || $status == 'deny' || $status == 'expire') {
    $order_status = 'cancelled';
} else if ($status == 'pending') {
    $order_status = 'pending';
}

// Update status di database
$sql = "UPDATE orders SET status = ? WHERE order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $order_status, $order_id);
mysqli_stmt_execute($stmt);

// Kirim response
header('HTTP/1.1 200 OK');
