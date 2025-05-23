<?php
require_once 'vendor/autoload.php';
require_once 'koneksi.php';
require_once 'session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Cek jika order_id ada
if (!isset($_GET['order_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Order ID tidak ditemukan']);
    exit();
}

$order_id = $_GET['order_id'];

// Ambil detail pesanan
$sql = "SELECT o.*, u.email, u.name 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Pesanan tidak ditemukan']);
    exit();
}

// Konfigurasi Midtrans
\Midtrans\Config::$serverKey = 'SB-Mid-server-5loh9cbN5XG4J80gx1nby1ml';
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

// Siapkan parameter transaksi
$transaction_details = array(
    'order_id' => $order_id,
    'gross_amount' => $order['grand_total']
);

$customer_details = array(
    'first_name' => $order['recipient_name'],
    'email' => $order['email'],
    'phone' => $order['phone'],
    'billing_address' => array(
        'address' => $order['address']
    )
);

// Item details
$item_details = array();
$sql = "SELECT oi.*, p.name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($item = mysqli_fetch_assoc($result)) {
    $item_details[] = array(
        'id' => $item['product_id'],
        'price' => $item['price'],
        'quantity' => $item['quantity'],
        'name' => $item['name']
    );
}

// Tambahkan biaya pengiriman ke item details
$item_details[] = array(
    'id' => 'shipping',
    'price' => $order['shipping_cost'],
    'quantity' => 1,
    'name' => 'Biaya Pengiriman'
);

// Tambahkan waktu expired (24 jam dari sekarang)
$expired_time = date('Y-m-d H:i:s', strtotime('+24 hours'));

$params = array(
    'transaction_details' => $transaction_details,
    'customer_details' => $customer_details,
    'item_details' => $item_details,
    'expiry' => array(
        'start_time' => date('Y-m-d H:i:s O'),
        'unit' => 'hour',
        'duration' => 24
    )
);

try {
    // Dapatkan Snap Token
    $snapToken = \Midtrans\Snap::getSnapToken($params);

    // Update status pesanan dan waktu expired
    $update_sql = "UPDATE orders SET payment_token = ?, expired_at = ? WHERE order_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($update_stmt, "ssi", $snapToken, $expired_time, $order_id);
    mysqli_stmt_execute($update_stmt);

    // Kirim response
    header('Content-Type: application/json');
    echo json_encode(['token' => $snapToken]);
} catch (Exception $e) {
    // Log error untuk debugging
    error_log("Midtrans Error: " . $e->getMessage());

    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
