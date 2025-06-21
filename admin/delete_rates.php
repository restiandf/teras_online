<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil ID rating dari URL
$rating_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Hapus rating
$sql = "DELETE FROM ratings WHERE rating_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $rating_id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: rates.php?success=2");
} else {
    header("Location: rates.php?error=1");
}
exit();
