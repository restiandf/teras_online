<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $province_id = $_POST['province'];
    $city_id = $_POST['city'];
    $address = $_POST['address'];
    $courier = $_POST['courier'];
    $payment_method = $_POST['payment_method'];

    // Ambil nama provinsi dan kota dari RajaOngkir
    $api_key = "568f1a71a258527138ddf23a2eba5945";
    $base_url = "https://api.rajaongkir.com/starter";

    // Ambil nama provinsi
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "$base_url/province?id=$province_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "key: $api_key"
        ),
    ));
    $response = curl_exec($curl);
    $province_data = json_decode($response, true);
    $province_name = $province_data['rajaongkir']['results']['province'];

    // Ambil nama kota
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "$base_url/city?id=$city_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "key: $api_key"
        ),
    ));
    $response = curl_exec($curl);
    $city_data = json_decode($response, true);
    $city_name = $city_data['rajaongkir']['results']['type'] . ' ' . $city_data['rajaongkir']['results']['city_name'];

    try {
        // Mulai transaksi
        mysqli_begin_transaction($conn);

        // Ambil data keranjang
        $sql = "SELECT c.cart_id, ci.item_id, ci.quantity, p.product_id, p.name, p.price 
                FROM cart c 
                JOIN cart_items ci ON c.cart_id = ci.cart_id 
                JOIN products p ON ci.product_id = p.product_id 
                WHERE c.user_id = ? AND c.status = 'active'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $cart_items = mysqli_fetch_all($result, MYSQLI_ASSOC);

        if (empty($cart_items)) {
            throw new Exception("Keranjang belanja kosong");
        }

        // Hitung total
        $total_amount = 0;
        foreach ($cart_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        // Hitung biaya pengiriman
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "$base_url/cost",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=151&destination=$city_id&weight=1000&courier=$courier",
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "key: $api_key"
            ),
        ));
        $response = curl_exec($curl);
        $shipping_data = json_decode($response, true);
        $shipping_cost = $shipping_data['rajaongkir']['results'][0]['costs'][0]['cost'][0]['value'];

        // Hitung grand total
        $grand_total = $total_amount + $shipping_cost;

        // Insert ke tabel orders
        $sql = "INSERT INTO orders (user_id, recipient_name, phone, address, payment_method, total_amount, shipping_cost, grand_total, 
                province_id, city_id, province_name, city_name, courier) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "issssdddsssss",
            $user_id,
            $name,
            $phone,
            $address,
            $payment_method,
            $total_amount,
            $shipping_cost,
            $grand_total,
            $province_id,
            $city_id,
            $province_name,
            $city_name,
            $courier
        );
        mysqli_stmt_execute($stmt);
        $order_id = mysqli_insert_id($conn);

        // Insert items ke order_items
        foreach ($cart_items as $item) {
            $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            mysqli_stmt_execute($stmt);

            // Update stok produk
            $sql = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['product_id']);
            mysqli_stmt_execute($stmt);
        }

        // Update status keranjang
        $sql = "UPDATE cart SET status = 'checkout' WHERE user_id = ? AND status = 'active'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);

        // Commit transaksi
        mysqli_commit($conn);

        // Redirect ke halaman sukses
        header("Location: order_success.php?order_id=" . $order_id);
        exit();
    } catch (Exception $e) {
        // Rollback jika terjadi error
        mysqli_rollback($conn);
        header("Location: checkout.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: checkout.php");
    exit();
}
