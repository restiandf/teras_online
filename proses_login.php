<?php
session_start(); // Mulai session

include 'koneksi.php'; // Sertakan file koneksi database

// Cek apakah form telah disubmit menggunakan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi data
    if (empty($email) || empty($password)) {
        // Jika ada field yang kosong, redirect kembali ke halaman login dengan pesan error
        header("Location: login.php?error=emptyfields");
        exit();
    }

    // Siapkan query SQL untuk mengambil data pengguna berdasarkan email
    $sql = "SELECT user_id, name, email, password, role FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        // Bind parameter
        mysqli_stmt_bind_param($stmt, "s", $email);

        // Eksekusi statement
        mysqli_stmt_execute($stmt);

        // Ambil hasil query
        $result = mysqli_stmt_get_result($stmt);

        // Cek apakah pengguna ditemukan
        if ($row = mysqli_fetch_assoc($result)) {
            // Verifikasi password
            if (password_verify($password, $row['password'])) {
                // Password cocok, login berhasil
                // Set session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_email'] = $row['email'];
                $_SESSION['user_role'] = $row['role'];

                // Redirect berdasarkan role
                if ($row['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                // Password salah
                header("Location: login.php?error=invalidcredentials");
                exit();
            }
        } else {
            // Pengguna tidak ditemukan
            header("Location: login.php?error=invalidcredentials");
            exit();
        }

        // Tutup statement
        mysqli_stmt_close($stmt);
    } else {
        // Error pada prepared statement
        header("Location: login.php?error=dberror");
        exit();
    }
} else {
    // Jika diakses langsung tanpa melalui POST method dari form
    header("Location: login.php");
    exit();
}
