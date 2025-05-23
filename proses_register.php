<?php
// Include file koneksi database
include 'koneksi.php';

// Cek apakah form telah disubmit menggunakan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password']; // Sesuaikan nama field jika berbeda

    // Validasi data
    if (empty($nama) || empty($email) || empty($password) || empty($konfirmasi_password)) {
        // Jika ada field yang kosong, redirect kembali ke halaman register dengan pesan error
        header("Location: register.php?error=emptyfields");
        exit();
    }

    // Cek apakah password dan konfirmasi password cocok
    if ($password !== $konfirmasi_password) {
        // Jika tidak cocok, redirect kembali dengan pesan error
        header("Location: register.php?error=passwordcheck&nama=" . urlencode($nama) . "&email=" . urlencode($email));
        exit();
    }

    // Cek apakah email sudah terdaftar
    $sql_check_email = "SELECT user_id FROM users WHERE email = ?";
    $stmt_check_email = mysqli_prepare($conn, $sql_check_email);
    mysqli_stmt_bind_param($stmt_check_email, "s", $email);
    mysqli_stmt_execute($stmt_check_email);
    mysqli_stmt_store_result($stmt_check_email);

    if (mysqli_stmt_num_rows($stmt_check_email) > 0) {
        // Jika email sudah terdaftar, redirect kembali dengan pesan error
        header("Location: register.php?error=emailtaken&nama=" . urlencode($nama));
        exit();
    } else {
        // Hash password sebelum disimpan ke database
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // PASSWORD_DEFAULT menggunakan algoritma hashing yang kuat saat ini

        // Siapkan query SQL untuk memasukkan data pengguna baru
        $sql_insert_user = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'pembeli')"; // Default role 'pembeli'

        // Gunakan prepared statement untuk keamanan
        $stmt_insert_user = mysqli_prepare($conn, $sql_insert_user);

        // Bind parameter ke statement
        mysqli_stmt_bind_param($stmt_insert_user, "sss", $nama, $email, $hashed_password);

        // Eksekusi statement
        if (mysqli_stmt_execute($stmt_insert_user)) {
            // Jika pendaftaran berhasil
            // Redirect ke halaman login atau halaman sukses
            header("Location: login.php?registration=success");
            exit();
        } else {
            // Jika terjadi error saat menyimpan ke database
            header("Location: register.php?error=dberror");
            exit();
        }
    }

    // Tutup prepared statements
    mysqli_stmt_close($stmt_check_email);
    mysqli_stmt_close($stmt_insert_user);
} else {
    // Jika diakses langsung tanpa melalui POST method dari form
    header("Location: register.php");
    exit();
}

// Tutup koneksi database (sudah ada di koneksi.php, tapi bisa ditambahkan di sini juga jika perlu)
// mysqli_close($conn);
