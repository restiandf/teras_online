<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if (isset($_GET['id'])) {
    $message_id = (int)$_GET['id'];

    // Hapus pesan
    $sql = "DELETE FROM messages WHERE message_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $message_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kontak.php?success=deleted");
    } else {
        header("Location: kontak.php?error=dberror");
    }
} else {
    header("Location: kontak.php?error=invalid");
}
exit();
