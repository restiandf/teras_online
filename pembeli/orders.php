<?php
require_once '../vendor/autoload.php';
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include 'inc_header.php';
include '../koneksi.php';

// Ambil data pesanan dari database
$user_id = $_SESSION['user_id'];

// Cek status pembayaran untuk pesanan yang pending
$sql = "SELECT order_id FROM orders WHERE user_id = ? AND status = 'pending' AND payment_token IS NOT NULL";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pending_orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

foreach ($pending_orders as $order) {
    try {
        require_once '../vendor/autoload.php';
        \Midtrans\Config::$serverKey = 'SB-Mid-server-5loh9cbN5XG4J80gx1nby1ml';
        \Midtrans\Config::$isProduction = false;

        $status = \Midtrans\Transaction::status($order['order_id']);
        $status_array = json_decode(json_encode($status), true);

        if ($status_array['transaction_status'] === 'settlement' || $status_array['transaction_status'] === 'capture') {
            $new_status = 'paid';
        } else if ($status_array['transaction_status'] === 'expire' || $status_array['transaction_status'] === 'cancel' || $status_array['transaction_status'] === 'deny') {
            $new_status = 'cancelled';
        } else {
            continue;
        }

        $update_sql = "UPDATE orders SET status = ? WHERE order_id = ?";
        $update_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "si", $new_status, $order['order_id']);
        mysqli_stmt_execute($update_stmt);
    } catch (Exception $e) {
        error_log("Error checking payment status: " . $e->getMessage());
    }
}

// Ambil data pesanan yang sudah diupdate
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_items,
        o.payment_token
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data produk untuk setiap pesanan
foreach ($orders as &$order) {
    $sql_items = "SELECT oi.*, p.name as product_name, p.price, pi.image_url 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.product_id 
                  LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
                  WHERE oi.order_id = ?";
    $stmt_items = mysqli_prepare($conn, $sql_items);
    mysqli_stmt_bind_param($stmt_items, "i", $order['order_id']);
    mysqli_stmt_execute($stmt_items);
    $result_items = mysqli_stmt_get_result($stmt_items);
    $order['items'] = mysqli_fetch_all($result_items, MYSQLI_ASSOC);
}
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- end of navbar -->

    <div class="container mx-auto px-4 pt-24 pb-12">
        <div class="max-w-7xl mx-auto border border-gray-200 rounded-lg p-6 shadow-md">
            <!-- Header Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6 bg-green-500 border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                            <span class="material-symbols-rounded">shopping_bag</span>
                            Daftar Pesanan
                        </h1>
                        <p class="text-white mt-1">Kelola dan pantau status pesanan Anda</p>
                    </div>
                    <a href="dashboard.php" class="bg-green-600 border border-green-600 rounded-md px-4 py-2 text-white hover:bg-green-600 hover:shadow-md transition-all duration-300 flex items-center gap-1">
                        <span class="material-symbols-rounded text-sm">arrow_back</span>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>

            <?php if (empty($orders)): ?>
                <!-- Tampilan ketika belum ada pesanan -->
                <div class="bg-white rounded-lg shadow-sm p-8 mb-4">
                    <div class="flex flex-col items-center justify-center py-8">
                        <div class="bg-gray-50 p-4 rounded-full mb-4">
                            <span class="material-symbols-rounded text-gray-400 text-5xl">
                                shopping_bag
                            </span>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-600 mb-2">Belum Ada Pesanan</h2>
                        <p class="text-gray-500 mb-6 text-center">Anda belum memiliki pesanan. Mulai belanja untuk melihat pesanan Anda di sini.</p>
                        <a href="../index.php" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition-colors duration-200 flex items-center gap-2">
                            <span class="material-symbols-rounded">shopping_cart</span>
                            Mulai Belanja
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Total Pesanan</p>
                                <p class="text-2xl font-bold text-gray-900"><?php echo count($orders); ?></p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <span class="material-symbols-rounded text-blue-600">shopping_bag</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Menunggu Pembayaran</p>
                                <p class="text-2xl font-bold text-yellow-600">
                                    <?php echo count(array_filter($orders, function ($o) {
                                        return $o['status'] == 'pending';
                                    })); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-yellow-100 rounded-full">
                                <span class="material-symbols-rounded text-yellow-600">payments</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Dalam Pengiriman</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    <?php echo count(array_filter($orders, function ($o) {
                                        return $o['status'] == 'shipped';
                                    })); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <span class="material-symbols-rounded text-purple-600">local_shipping</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600">Selesai</p>
                                <p class="text-2xl font-bold text-green-600">
                                    <?php echo count(array_filter($orders, function ($o) {
                                        return $o['status'] == 'delivered';
                                    })); ?>
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <span class="material-symbols-rounded text-green-600">check_circle</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tampilan daftar pesanan -->
                <div class="space-y-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                            <span class="material-symbols-rounded text-gray-400">receipt_long</span>
                                            Pesanan #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                                        </h2>
                                        <p class="text-sm text-gray-500 mt-1">
                                            <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?>
                                        </p>
                                        <?php if ($order['status'] === 'pending' && isset($order['expired_at']) && $order['expired_at']): ?>
                                            <p class="text-sm text-red-500 mt-1 flex items-center gap-1">
                                                <span class="material-symbols-rounded text-sm">timer</span>
                                                Batas pembayaran: <?php echo date('d F Y H:i', strtotime($order['expired_at'])); ?>
                                            </p>
                                        <?php endif; ?>
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
                                            <span class="material-symbols-rounded text-sm mr-1">
                                                <?php
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        echo 'payments';
                                                        break;
                                                    case 'paid':
                                                        echo 'check_circle';
                                                        break;
                                                    case 'processing':
                                                        echo 'pending';
                                                        break;
                                                    case 'shipped':
                                                        echo 'local_shipping';
                                                        break;
                                                    case 'delivered':
                                                        echo 'done_all';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'cancel';
                                                        break;
                                                    default:
                                                        echo 'info';
                                                }
                                                ?>
                                            </span>
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

                                <div class="border-t pt-3">
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <h3 class="font-medium text-gray-700 mb-2 flex items-center gap-1 text-sm">
                                                <span class="material-symbols-rounded text-gray-400 text-base">shopping_cart</span>
                                                Daftar Produk
                                            </h3>
                                            <div class="rounded-lg">
                                                <div class="space-y-2">
                                                    <?php foreach ($order['items'] as $item): ?>
                                                        <div class="flex items-center gap-2 p-1 bg-white rounded-lg border border-gray-100">
                                                            <div class="w-8 h-8 flex-shrink-0">
                                                                <?php if (!empty($item['image_url'])): ?>
                                                                    <img src="../<?php echo htmlspecialchars($item['image_url']); ?>"
                                                                        alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                                        class="w-full h-full object-cover rounded-lg">
                                                                <?php else: ?>
                                                                    <div class="w-full h-full bg-gray-200 rounded-lg flex items-center justify-center">
                                                                        <span class="material-symbols-rounded text-gray-400 text-sm">image</span>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="flex-grow">
                                                                <h4 class="font-medium text-gray-800 text-xs"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                                                                <div class="flex items-center gap-1 text-xs text-gray-600">
                                                                    <span><?php echo $item['quantity']; ?></span>
                                                                    <span>x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="text-right">
                                                                <p class="font-medium text-gray-800 text-sm">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-700 mb-2 flex items-center gap-1 text-sm">
                                                <span class="material-symbols-rounded text-gray-400 text-base">local_shipping</span>
                                                Detail Pengiriman
                                            </h3>
                                            <div class="space-y-1">
                                                <p class="text-gray-600 flex items-center gap-1 text-sm">
                                                    <span class="material-symbols-rounded text-gray-400 text-sm">person</span>
                                                    <?php echo htmlspecialchars($order['recipient_name']); ?>
                                                </p>
                                                <p class="text-gray-600 flex items-center gap-1 text-sm">
                                                    <span class="material-symbols-rounded text-gray-400 text-sm">phone</span>
                                                    <?php echo htmlspecialchars($order['phone']); ?>
                                                </p>
                                                <p class="text-gray-600 flex items-center gap-1 text-sm">
                                                    <span class="material-symbols-rounded text-gray-400 text-sm">location_on</span>
                                                    <?php echo htmlspecialchars($order['address']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div>
                                            <h3 class="font-medium text-gray-700 mb-2 flex items-center gap-1 text-sm">
                                                <span class="material-symbols-rounded text-gray-400 text-base">payments</span>
                                                Detail Pembayaran
                                            </h3>
                                            <div class="space-y-1">
                                                <p class="text-gray-600 flex items-center gap-1 text-sm">
                                                    <span class="material-symbols-rounded text-gray-400 text-sm">payment</span>
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
                                                <p class="text-gray-600 flex items-center gap-1 text-sm">
                                                    <span class="material-symbols-rounded text-gray-400 text-sm">inventory_2</span>
                                                    Total Item: <?php echo $order['total_items']; ?>
                                                </p>
                                                <p class="text-gray-600 flex items-center gap-1 text-sm">
                                                    <span class="material-symbols-rounded text-gray-400 text-sm">payments</span>
                                                    Total: Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="border-t pt-4 mt-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="order_detail.php?id=<?php echo $order['order_id']; ?>"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                            <span class="material-symbols-rounded text-sm mr-1">visibility</span>
                                            Lihat Detail
                                        </a>
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button onclick="payOrder(<?php echo $order['order_id']; ?>)"
                                                class="gap-2 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200">
                                                <span class="material-symbols-rounded text-sm">payments</span>
                                                Bayar Sekarang
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($order['status'] !== 'cancelled'): ?>
                                            <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" target="_blank"
                                                class="gap-2 inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors duration-200">
                                                <span class="material-symbols-rounded text-sm">print</span>
                                                Cetak Invoice
                                            </a>
                                        <?php endif; ?>
                                    </div>
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