<?php
require_once 'koneksi.php';

// Update status pesanan yang expired
$sql = "UPDATE orders 
        SET status = 'cancelled' 
        WHERE status = 'pending' 
        AND expired_at < NOW()";
$result = mysqli_query($conn, $sql);

if ($result) {
    echo "Status pesanan berhasil diperbarui";
} else {
    echo "Error: " . mysqli_error($conn);
}
