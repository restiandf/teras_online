<?php
include 'session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada order_id
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

include 'inc_header.php';
include 'koneksi.php';

// Ambil data pesanan
$order_id = $_GET['order_id'];
$sql = "SELECT o.*, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: index.php");
    exit();
}
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
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Pesanan Berhasil!</h1>
                <p class="text-gray-600 mb-6">
                    Terima kasih telah berbelanja di Teras Online. Pesanan Anda sedang diproses.
                </p>

                <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Pesanan</h2>
                    <div class="space-y-2">
                        <p><span class="font-medium">Nomor Pesanan:</span> #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></p>
                        <p><span class="font-medium">Tanggal:</span> <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></p>
                        <p><span class="font-medium">Total Pembayaran:</span> Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></p>
                        <p><span class="font-medium">Metode Pembayaran:</span>
                            <?php
                            switch ($order['payment_method']) {
                                case 'transfer':
                                    echo 'Transfer Bank';
                                    break;
                                case 'ewallet':
                                    echo 'E-Wallet';
                                    break;
                                case 'cod':
                                    echo 'Cash on Delivery';
                                    break;
                                default:
                                    echo ucfirst($order['payment_method']);
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <?php if ($order['payment_method'] !== 'cod'): ?>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <span class="material-symbols-rounded text-yellow-400">
                                    info
                                </span>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    Silakan lakukan pembayaran sesuai metode yang dipilih. Pesanan akan diproses setelah pembayaran dikonfirmasi.
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="flex justify-center space-x-4">
                    <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Lanjut Belanja
                    </a>
                    <a href="pembeli/orders.php" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Lihat Pesanan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- footer -->
    <?php include 'inc_footer.php'; ?>
    <!-- End Of Footer -->

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>