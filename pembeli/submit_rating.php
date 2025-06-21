<?php
include '../session.php';
include '../koneksi.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Anda harus login.']);
    exit();
}
$user_id = $_SESSION['user_id'];
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
if ($product_id <= 0 || $order_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    exit();
}
// Cek apakah user sudah pernah memberi rating untuk produk ini di order ini
$sql = "SELECT rating_id FROM ratings WHERE product_id = ? AND user_id = ? AND order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iii", $product_id, $user_id, $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_fetch_assoc($result)) {
    echo json_encode(['success' => false, 'message' => 'Anda sudah memberi rating untuk produk ini.']);
    exit();
}
// Simpan rating
$sql = "INSERT INTO ratings (product_id, user_id, order_id, rating, comment, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "iiiis", $product_id, $user_id, $order_id, $rating, $comment);
if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan rating.']);
}
