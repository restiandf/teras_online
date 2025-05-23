<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    
    // Ambil data gambar sebelum menghapus
    $sql = "SELECT image_url FROM product_images WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    // Hapus file gambar
    while($row = mysqli_fetch_assoc($result)) {
        $image_path = "../" . $row['image_url'];
        if(file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Hapus data dari database
    // Karena ada foreign key constraint, hapus dari product_images dulu
    $sql = "DELETE FROM product_images WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    
    // Kemudian hapus dari products
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: products.php?success=deleted");
    } else {
        header("Location: products.php?error=dberror");
    }
} else {
    header("Location: products.php?error=invalid");
}
exit(); 