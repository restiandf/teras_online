<?php
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($subject) || empty($message)) {
        header("Location: laporan.php?error=empty");
        exit();
    }

    // Ambil data user
    $sql = "SELECT name, email FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // Simpan pesan ke database
    $sql = "INSERT INTO messages (user_id, name, email, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, 'unread', NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $user_id, $user['name'], $user['email'], $subject, $message);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: laporan.php?success=sent");
    } else {
        header("Location: laporan.php?error=dberror");
    }
} else {
    header("Location: laporan.php?error=invalid");
}
exit();
