<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include '../koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['image_id'])) {
    $image_id = $_POST['image_id'];

    // Ambil informasi gambar sebelum dihapus
    $sql_get_image = "SELECT image_url, product_id, is_primary FROM product_images WHERE image_id = ?";
    $stmt_get = mysqli_prepare($conn, $sql_get_image);
    mysqli_stmt_bind_param($stmt_get, "i", $image_id);
    mysqli_stmt_execute($stmt_get);
    $result = mysqli_stmt_get_result($stmt_get);
    $image = mysqli_fetch_assoc($result);

    if ($image) {
        // Hapus file fisik
        $file_path = '../' . $image['image_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Hapus dari database
        $sql_delete = "DELETE FROM product_images WHERE image_id = ?";
        $stmt_delete = mysqli_prepare($conn, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $image_id);

        if (mysqli_stmt_execute($stmt_delete)) {
            // Jika gambar yang dihapus adalah primary, set gambar lain sebagai primary
            if ($image['is_primary']) {
                $sql_update_primary = "UPDATE product_images SET is_primary = 1 WHERE product_id = ? AND image_id != ? LIMIT 1";
                $stmt_update = mysqli_prepare($conn, $sql_update_primary);
                mysqli_stmt_bind_param($stmt_update, "ii", $image['product_id'], $image_id);
                mysqli_stmt_execute($stmt_update);
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari database']);
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Gambar tidak ditemukan']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
exit();
