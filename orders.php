<?php
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../inc_header.php';
include '../koneksi.php';

// Ambil data pesanan dari database
$user_id = $_SESSION['user_id'];
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_items
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- end of navbar -->

    <div class="container mx-auto px-4 pt-24 pb-12">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Daftar Pesanan</h1>
                <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">
                    <span class="material-symbols-rounded text-sm mr-1">arrow_back</span>
                    Kembali ke Dashboard
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <!-- Tampilan ketika belum ada pesanan -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                    <div class="flex flex-col items-center justify-center py-8">
                        <span class="material-symbols-rounded text-gray-400 text-6xl mb-4">
                            shopping_bag
                        </span>
                        <h2 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Pesanan</h2>
                        <p class="text-gray-500 mb-6">Anda belum memiliki pesanan</p>
                        <a href="../index.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            Mulai Belanja
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Tampilan daftar pesanan -->
                <div class="space-y-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-800">
                                        Pesanan #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                        <?php
                                        switch ($order['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'paid':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'processing':
                                                echo 'bg-purple-100 text-purple-800';
                                                break;
                                            case 'shipped':
                                                echo 'bg-indigo-100 text-indigo-800';
                                                break;
                                            case 'delivered':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php
                                        switch ($order['status']) {
                                            case 'pending':
                                                echo 'Menunggu Pembayaran';
                                                break;
                                            case 'paid':
                                                echo 'Sudah Dibayar';
                                                break;
                                            case 'processing':
                                                echo 'Sedang Diproses';
                                                break;
                                            case 'shipped':
                                                echo 'Dalam Pengiriman';
                                                break;
                                            case 'delivered':
                                                echo 'Selesai';
                                                break;
                                            case 'cancelled':
                                                echo 'Dibatalkan';
                                                break;
                                            default:
                                                echo ucfirst($order['status']);
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="border-t pt-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h3 class="font-medium text-gray-700 mb-2">Detail Pengiriman</h3>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($order['recipient_name']); ?></p>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($order['phone']); ?></p>
                                        <p class="text-gray-600"><?php echo htmlspecialchars($order['address']); ?></p>
                                    </div>
                                    <div>
                                        <h3 class="font-medium text-gray-700 mb-2">Detail Pembayaran</h3>
                                        <p class="text-gray-600">
                                            Metode:
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
                                        <p class="text-gray-600">Total Item: <?php echo $order['total_items']; ?></p>
                                        <p class="text-gray-600">Total: Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t pt-4 mt-4">
                                <div class="flex justify-end space-x-4">
                                    <a href="order_detail.php?id=<?php echo $order['order_id']; ?>"
                                        class="text-blue-600 hover:text-blue-800">
                                        Lihat Detail
                                    </a>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <button onclick="payOrder(<?php echo $order['order_id']; ?>)"
                                            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                            Bayar Sekarang
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- footer -->
    <?php include '../inc_footer.php'; ?>
    <!-- End Of Footer -->

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-_HbPiG1xfD8K8cQT"></script>

    <script>
        function payOrder(orderId) {
            // Ambil token transaksi dari server
            fetch('../get_snap_token.php?order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.token) {
                        snap.pay(data.token, {
                            onSuccess: function(result) {
                                // Handle success
                                window.location.href = 'order_success.php?order_id=' + orderId;
                            },
                            onPending: function(result) {
                                // Handle pending
                                alert('Pembayaran masih pending');
                            },
                            onError: function(result) {
                                // Handle error
                                alert('Pembayaran gagal');
                            },
                            onClose: function() {
                                // Handle customer closed the popup without finishing the payment
                                alert('Anda menutup popup tanpa menyelesaikan pembayaran');
                            }
                        });
                    } else {
                        alert('Gagal mendapatkan token pembayaran');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses pembayaran');
                });
        }
    </script>
</body>

</html>