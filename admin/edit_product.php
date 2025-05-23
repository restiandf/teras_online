<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];

    // Validasi input
    if (empty($name) || empty($price) || empty($stock) || empty($description)) {
        header("Location: products.php?error=emptyfields");
        exit();
    }

    // Update data produk
    $sql = "UPDATE products SET name = ?, price = ?, stock = ?, description = ? WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "siisi", $name, $price, $stock, $description, $product_id);

    if (mysqli_stmt_execute($stmt)) {
        $upload_success = true;

        // Proses upload gambar baru jika ada
        if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 2 * 1024 * 1024; // 2MB

            // Buat direktori img/products jika belum ada
            $upload_dir = '../img/products';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Cek apakah sudah ada gambar primary
            $sql_check = "SELECT COUNT(*) as count FROM product_images WHERE product_id = ? AND is_primary = 1";
            $stmt_check = mysqli_prepare($conn, $sql_check);
            mysqli_stmt_bind_param($stmt_check, "i", $product_id);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $row_check = mysqli_fetch_assoc($result_check);
            $has_primary = $row_check['count'] > 0;

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
                        $is_primary = !$has_primary ? 1 : 0; // Set sebagai primary jika belum ada
                        $has_primary = true;

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
            header("Location: products.php?success=updated");
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
