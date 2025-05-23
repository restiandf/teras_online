<?php
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Cek apakah ada ID pesanan
if (!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

include 'inc_header.php';
include '../koneksi.php';

// Ambil detail pesanan
$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Update status pesanan menjadi 'paid'
$update_sql = "UPDATE orders SET status = 'paid' WHERE order_id = ?";
$update_stmt = mysqli_prepare($conn, $update_sql);
mysqli_stmt_bind_param($update_stmt, "i", $order_id);
mysqli_stmt_execute($update_stmt);
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- end of navbar -->

    <div class="container mx-auto px-4 pt-24 pb-12">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <div class="mb-6">
                    <span class="material-symbols-rounded text-green-500 text-6xl">
                        check_circle
                    </span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Pembayaran Berhasil!</h1>
                <p class="text-gray-600 mb-6">
                    Terima kasih telah berbelanja di Teras Online. Pesanan Anda akan segera kami proses.
                </p>
                <div class="space-y-4">
                    <p class="text-gray-700">
                        Nomor Pesanan: <span class="font-semibold">#<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></span>
                    </p>
                    <p class="text-gray-700">
                        Total Pembayaran: <span class="font-semibold">Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></span>
                    </p>
                </div>
                <div class="mt-8 space-x-4">
                    <a href="orders.php" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Lihat Pesanan
                    </a>
                    <a href="../index.php" class="inline-block bg-gray-200 text-gray-800 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors">
                        Lanjut Belanja
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- footer -->
    <?php include '../inc_footer.php'; ?>
    <!-- End Of Footer -->

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>