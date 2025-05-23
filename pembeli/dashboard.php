<?php
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'inc_header.php';
include '../koneksi.php';

// Ambil data pesanan terbaru
$user_id = $_SESSION['user_id'];
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_items
        FROM orders o 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC 
        LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$recent_orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Hitung total pesanan
$sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_orders = mysqli_fetch_assoc($result)['total'];

// Hitung total pembelian
$sql = "SELECT SUM(grand_total) as total FROM orders WHERE user_id = ? AND status != 'cancelled'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$total_spent = mysqli_fetch_assoc($result)['total'] ?? 0;
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- end of navbar -->

    <div class="container mx-auto px-4 pt-24 pb-12 ">
        <div class="max-w-7xl mx-auto rounded-lg border border-gray-200 shadow-md p-6">
            <div class="bg-white rounded-lg p-6 mb-6 bg-blue-600 border border-blue-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                            <span class="material-symbols-rounded">dashboard</span>
                            Dashboard
                        </h1>
                        <p class="text-white/90 mt-1 flex items-center gap-2 italic">
                            <span class="material-symbols-rounded">waving_hand</span>
                            Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! ✨ Semoga harimu menyenangkan! 🌟
                        </p>
                    </div>
                </div>
            </div>
            <!-- Statistik -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg p-6 border border-blue-300 bg-blue-100">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                            <span class="material-symbols-rounded text-3xl">shopping_bag</span>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-blue-600">Total Pesanan</h2>
                            <p class="text-2xl font-semibold text-blue-800"><?php echo $total_orders; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-100 rounded-lg p-6 border border-green-300">
                    <div class="flex items-center bor">
                        <div class="p-3 rounded-full bg-green-200 text-green-600">
                            <span class="material-symbols-rounded text-3xl">payments</span>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-green-600">Total Pembelian</h2>
                            <p class="text-2xl font-semibold text-green-800">Rp <?php echo number_format($total_spent, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pesanan Terbaru -->
            <div class="bg-white rounded-lg p-6 border border-gray-200">
                <div class="flex justify-between items-center mb-6 rounded-lg bg-blue-50 p-4">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <span class="material-symbols-rounded text-blue-600">receipt_long</span>
                        Pesanan Terbaru
                    </h2>
                    <a href="orders.php" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-all duration-300 hover:gap-2 hover:bg-blue-50 px-3 py-1 rounded-full">
                        Lihat Semua
                        <span class="material-symbols-rounded">arrow_right_alt</span>
                    </a>
                </div>

                <?php if (empty($recent_orders)): ?>
                    <div class="text-center py-8">
                        <span class="material-symbols-rounded text-gray-400 text-6xl mb-4">
                            shopping_bag
                        </span>
                        <p class="text-gray-500">Belum ada pesanan</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">

                        <?php foreach ($recent_orders as $loop => $order): ?>
                            <div class="flex items-center justify-between border-b pb-4">
                                <div class="flex items-center gap-4">
                                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-semibold text-sm"><?php echo $loop + 1; ?></span>
                                    <div>
                                        <h3 class="font-medium text-gray-800">
                                            Pesanan #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
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
                                    <p class="text-gray-600 mt-1">
                                        Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- footer -->
    <?php include '../inc_footer.php'; ?>
    <!-- End Of Footer -->

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>