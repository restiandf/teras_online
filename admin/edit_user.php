<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $new_password = $_POST['new_password'];

    // Cek apakah email sudah terdaftar (kecuali untuk user yang sedang diedit)
    $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND user_id != $user_id");
    if (mysqli_num_rows($check_email) > 0) {
        header("Location: pengguna.php?error=email");
        exit();
    }

    // Update user
    if (!empty($new_password)) {
        // Jika ada password baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name = ?, email = ?, password = ?, role = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssi", $name, $email, $hashed_password, $role, $user_id);
    } else {
        // Jika tidak ada password baru
        $sql = "UPDATE users SET name = ?, email = ?, role = ? WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $role, $user_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("Location: pengguna.php?success=updated");
    } else {
        header("Location: pengguna.php?error=dberror");
    }
} else {
    header("Location: pengguna.php");
}
exit();
