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
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $user_id = $_SESSION['user_id'];

    // Validasi input
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Data tidak valid']);
        exit();
    }

    try {
        // Cek stok produk
        $sql = "SELECT stock FROM products WHERE product_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $product_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);

        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Produk tidak ditemukan']);
            exit();
        }

        if ($product['stock'] < $quantity) {
            echo json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi']);
            exit();
        }

        // Cek apakah user memiliki cart aktif
        $sql = "SELECT cart_id FROM cart WHERE user_id = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "i", $user_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $cart = mysqli_fetch_assoc($result);

        if (!$cart) {
            // Buat cart baru jika belum ada
            $sql = "INSERT INTO cart (user_id) VALUES (?)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "i", $user_id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
            }

            $cart_id = mysqli_insert_id($conn);
        } else {
            $cart_id = $cart['cart_id'];
        }

        // Cek apakah produk sudah ada di cart
        $sql = "SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            throw new Exception("Error preparing statement: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ii", $cart_id, $product_id);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $cart_item = mysqli_fetch_assoc($result);

        if ($cart_item) {
            // Update quantity jika produk sudah ada di cart
            $new_quantity = $cart_item['quantity'] + $quantity;
            if ($new_quantity > $product['stock']) {
                echo json_encode(['status' => 'error', 'message' => 'Stok tidak mencukupi']);
                exit();
            }

            $sql = "UPDATE cart_items SET quantity = ? WHERE cart_id = ? AND product_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "iii", $new_quantity, $cart_id, $product_id);
        } else {
            // Tambah produk baru ke cart
            $sql = "INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "iii", $cart_id, $product_id, $quantity);
        }

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error executing statement: " . mysqli_stmt_error($stmt));
        }

        echo json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan ke keranjang']);
    } catch (Exception $e) {
        error_log("Error in add_to_cart.php: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method tidak diizinkan']);
}
