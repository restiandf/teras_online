<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    // Validasi input
    if (empty($name) || empty($price) || empty($stock) || empty($description)) {
        header("Location: products.php?error=emptyfields");
        exit();
    }

    // Insert data produk
    $sql = "INSERT INTO products (name, price, stock, description) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "siis", $name, $price, $stock, $description);

    if (mysqli_stmt_execute($stmt)) {
        $product_id = mysqli_insert_id($conn);
        $upload_success = true;
        $primary_set = false;

        // Proses upload gambar
        if (isset($_FILES['images'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB

            // Buat direktori img/products jika belum ada
            $upload_dir = '../img/products';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['images']['name'][$key];
                $file_type = $_FILES['images']['type'][$key];
                $file_size = $_FILES['images']['size'][$key];
                $file_error = $_FILES['images']['error'][$key];

                // Validasi file
                if ($file_error === 0) {
                    if (!in_array($file_type, $allowed_types)) {
                        $upload_success = false;
                        header("Location: products.php?error=wrongformat");
                        exit();
                    }

                    if ($file_size > $max_size) {
                        $upload_success = false;
                        header("Location: products.php?error=toobig");
                        exit();
                    }

                    // Generate nama file unik
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $new_file_name = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . '/' . $new_file_name;

                    // Upload file
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        // Simpan informasi gambar ke database
                        $image_url = 'img/products/' . $new_file_name;
                        $is_primary = !$primary_set ? 1 : 0; // Set gambar pertama sebagai primary
                        $primary_set = true;

                        $sql_image = "INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)";
                        $stmt_image = mysqli_prepare($conn, $sql_image);
                        mysqli_stmt_bind_param($stmt_image, "isi", $product_id, $image_url, $is_primary);
                        mysqli_stmt_execute($stmt_image);
                    } else {
                        $upload_success = false;
                        header("Location: products.php?error=uploadfailed");
                        exit();
                    }
                }
            }
        }

        if ($upload_success) {
            header("Location: products.php?success=added");
        } else {
            header("Location: products.php?error=uploadfailed");
        }
    } else {
        header("Location: products.php?error=dberror");
    }
} else {
    header("Location: products.php");
}
exit();
