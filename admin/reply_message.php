<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message_id = (int)$_POST['message_id'];
    $reply = trim($_POST['reply']);
    $user_email = $_POST['user_email'];
    $subject = "Re: " . $_POST['subject'];
    $admin_id = $_SESSION['user_id'];

    if (empty($reply)) {
        header("Location: kontak.php?error=empty");
        exit();
    }

    // Update status pesan menjadi 'read'
    $sql = "UPDATE messages SET status = 'read' WHERE message_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $message_id);
    mysqli_stmt_execute($stmt);

    // Simpan balasan ke database
    $sql = "INSERT INTO message_replies (message_id, admin_id, reply, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $message_id, $admin_id, $reply);

    if (!mysqli_stmt_execute($stmt)) {
        header("Location: kontak.php?error=dberror");
        exit();
    }

    // Kirim email balasan
    $to = $user_email;
    $headers = "From: admin@terasonline.com\r\n";
    $headers .= "Reply-To: admin@terasonline.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $message = "
    <html>
    <head>
        <title>Balasan dari Admin Teras Online</title>
    </head>
    <body>
        <h2>Balasan untuk pesan Anda:</h2>
        <p>" . nl2br(htmlspecialchars($reply)) . "</p>
        <hr>
        <p>Terima kasih telah menghubungi kami.</p>
        <p>Admin Teras Online</p>
    </body>
    </html>";

    if (mail($to, $subject, $message, $headers)) {
        header("Location: kontak.php?success=replied");
    } else {
        header("Location: kontak.php?error=mailerror");
    }
} else {
    header("Location: kontak.php?error=invalid");
}
exit();
