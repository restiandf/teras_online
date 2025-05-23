<?php
session_start(); // Mulai session

// Hapus semua session variables
$_SESSION = array();

// Jika menghapus cookie session juga diinginkan,
// perhatikan bahwa ini akan menghancurkan session, dan tidak hanya data session
// Juga perlu menghapus cookie session.
// Catatan: Ini akan membuat cookie session tidak valid, bukan menghapusnya dari browser.
// Untuk menghapus cookie session dari browser, Anda harus mengatur masa lalu.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman index
header("Location: index.php");
exit();
