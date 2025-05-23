<?php
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah ada ID pesanan
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

include 'inc_header.php';
include '../koneksi.php';

// Ambil detail pesanan
$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_items
        FROM orders o 
        WHERE o.order_id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Ambil detail item pesanan
$sql = "SELECT oi.*, p.name, p.price, pi.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order_items = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- end of navbar -->

    <div class="container mx-auto px-4 pt-24 pb-12">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-8">
                <h1 class="text-2xl font-bold text-gray-800">Detail Pesanan</h1>
                <a href="orders.php" class="gap-2 inline-flex items-center text-blue-600 hover:text-blue-800">
                    <span class="material-symbols-rounded">arrow_back</span>
                    Kembali ke Daftar Pesanan
                </a>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-start mb-6">
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">Detail Pengiriman</h3>
                        <p class="text-gray-600"><?php echo htmlspecialchars($order['recipient_name']); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($order['phone']); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($order['province_name']); ?></p>
                        <p class="text-gray-600"><?php echo htmlspecialchars($order['city_name']); ?></p>
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
                        <?php if ($order['status'] === 'pending' && !empty($order['payment_token'])): ?>
                            <div class="mt-2 p-3 bg-yellow-50 rounded-lg">
                                <p class="text-sm text-yellow-800 font-medium mb-1">Informasi Pembayaran:</p>
                                <?php
                                try {
                                    require_once '../vendor/autoload.php';
                                    \Midtrans\Config::$serverKey = 'SB-Mid-server-5loh9cbN5XG4J80gx1nby1ml';
                                    \Midtrans\Config::$isProduction = false;

                                    $status = \Midtrans\Transaction::status($order['order_id']);
                                    $status_array = json_decode(json_encode($status), true);

                                    if ($status_array['transaction_status'] === 'pending') {
                                        echo '<div class="space-y-2">';
                                        echo '<p class="text-sm text-yellow-800">Transaction ID: ' . $status_array['transaction_id'] . '</p>';
                                        echo '<p class="text-sm text-yellow-800">Channel: ' . $status_array['payment_type'] . '</p>';
                                        echo '<p class="text-sm text-yellow-800">Created on: ' . date('d M Y, H:i', strtotime($status_array['transaction_time'])) . '</p>';
                                        echo '<p class="text-sm text-yellow-800">Expiry time: ' . date('d M Y, H:i', strtotime($status_array['expiry_time'])) . '</p>';

                                        // Tampilkan VA number jika ada
                                        if (isset($status_array['va_numbers']) && !empty($status_array['va_numbers'])) {
                                            foreach ($status_array['va_numbers'] as $va) {
                                                echo '<p class="text-sm text-yellow-800">Virtual Account: ' . $va['va_number'] . '</p>';
                                                echo '<p class="text-sm text-yellow-800">Bank: ' . $va['bank'] . '</p>';
                                            }
                                        }
                                        echo '</div>';
                                    }
                                } catch (Exception $e) {
                                    error_log("Error getting payment info: " . $e->getMessage());
                                }
                                ?>
                                <p class="text-xs text-yellow-600 mt-2">Silakan lakukan pembayaran sesuai dengan informasi di atas</p>
                            </div>
                        <?php endif; ?>
                        <p class="text-gray-600 mt-3">Total Item: <?php echo $order['total_items']; ?></p>
                        <p class="text-gray-600">Subtotal: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                        <p class="text-gray-600">Biaya Pengiriman: Rp <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></p>
                        <p class="text-gray-600">Ekspedisi: <?php echo htmlspecialchars($order['courier']); ?></p>
                        <p class="text-gray-600 font-semibold">Total: Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></p>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h3 class="font-medium text-gray-700 mb-4">Item Pesanan</h3>
                    <div class="space-y-4">
                        <?php foreach ($order_items as $item): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <img src="<?php echo htmlspecialchars($item['image_url'] ? '../' . $item['image_url'] : '../img/no-image.jpg'); ?>"
                                        alt="<?php echo htmlspecialchars($item['name']); ?>"
                                        class="w-16 h-16 object-cover rounded">
                                    <div>
                                        <h4 class="font-medium text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h4>
                                        <p class="text-sm text-gray-500"><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                                <p class="font-medium text-gray-800">
                                    Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($order['status'] === 'pending'): ?>
                    <div class="border-t pt-6 mt-6">
                        <div class="flex justify-end space-x-4">
                            <button onclick="payOrder(<?php echo $order['order_id']; ?>)"
                                class="gap-2 inline-flex items-center bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                                <span class="material-symbols-rounded">payments</span>
                                Bayar Sekarang
                            </button>
                            <?php if ($order['status'] !== 'cancelled'): ?>
                                <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" target="_blank"
                                    class="gap-2 inline-flex items-center bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                    <span class="material-symbols-rounded">print</span>
                                    Cetak Invoice
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="border-t pt-6 mt-6">
                        <div class="flex justify-end">
                            <?php if ($order['status'] !== 'cancelled'): ?>
                                <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" target="_blank"
                                    class="gap-2 inline-flex items-center bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                    <span class="material-symbols-rounded">print</span>
                                    Cetak Invoice
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
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