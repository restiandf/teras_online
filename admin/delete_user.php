<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    // Cek apakah user yang akan dihapus bukan user yang sedang login
    if ($user_id === (int)$_SESSION['user_id']) {
        header("Location: pengguna.php?error=selfdelete");
        exit();
    }

    // Hapus user
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: pengguna.php?success=deleted");
    } else {
        header("Location: pengguna.php?error=dberror");
    }
} else {
    header("Location: pengguna.php");
}
exit();
