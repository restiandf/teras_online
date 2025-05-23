<?php
session_start();
include 'koneksi.php';

// Set header untuk JSON response
header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

// Cek apakah ada data yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = isset($_POST['item_id']) ? (int)$_POST['item_id'] : 0;

    // Validasi input
    if ($item_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
        exit();
    }

    try {
        // Hapus item dari keranjang
        $sql = "DELETE ci FROM cart_items ci 
                JOIN cart c ON ci.cart_id = c.cart_id 
                WHERE ci.item_id = ? AND c.user_id = ? AND c.status = 'active'";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ii", $item_id, $_SESSION['user_id']);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Item berhasil dihapus dari keranjang']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan']);
        }
    } catch (Exception $e) {
        error_log("Error in remove_cart_item.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
