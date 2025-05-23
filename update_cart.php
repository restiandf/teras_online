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
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Validasi input
    if ($item_id <= 0 || !in_array($action, ['increase', 'decrease'])) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
        exit();
    }

    try {
        // Ambil data item dan stok produk
        $sql = "SELECT ci.quantity, p.stock 
                FROM cart_items ci 
                JOIN products p ON ci.product_id = p.product_id 
                WHERE ci.item_id = ? AND ci.cart_id IN (
                    SELECT cart_id FROM cart WHERE user_id = ? AND status = 'active'
                )";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ii", $item_id, $_SESSION['user_id']);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $item = mysqli_fetch_assoc($result);

        if (!$item) {
            echo json_encode(['status' => 'error', 'message' => 'Item tidak ditemukan']);
            exit();
        }

        // Hitung quantity baru
        $new_quantity = $action === 'increase' ? $item['quantity'] + 1 : $item['quantity'] - 1;

        // Validasi quantity
        if ($new_quantity <= 0) {
            // Hapus item jika quantity 0 atau kurang
            $sql = "DELETE FROM cart_items WHERE item_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "i", $item_id);
        } else if ($new_quantity > $item['stock']) {
            echo json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi']);
            exit();
        } else {
            // Update quantity
            $sql = "UPDATE cart_items SET quantity = ? WHERE item_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $item_id);
        }

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        echo json_encode(['status' => 'success', 'message' => 'Keranjang berhasil diperbarui']);
    } catch (Exception $e) {
        error_log("Error in update_cart.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
