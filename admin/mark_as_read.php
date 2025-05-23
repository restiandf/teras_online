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

    // Update status pesan menjadi 'read'
    $sql = "UPDATE messages SET status = 'read' WHERE message_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $message_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: kontak.php?success=read");
    } else {
        header("Location: kontak.php?error=dberror");
    }
} else {
    header("Location: kontak.php?error=invalid");
}
exit();
