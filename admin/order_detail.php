<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

$order_id = $_GET['id'];

// Ambil detail order
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($result);

// Ambil items order
$sql = "SELECT oi.*, p.name as product_name, p.price, pi.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $order_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$items = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Proses update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $new_status = $_POST['status'];
        $tracking_number = $_POST['tracking_number'] ?? null;
        $courier = $_POST['courier'] ?? null;

        $sql = "UPDATE orders SET status = ?, tracking_number = ?, courier = ? WHERE order_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $new_status, $tracking_number, $courier, $order_id);

        if (mysqli_stmt_execute($stmt)) {
            // Refresh halaman untuk menampilkan status terbaru
            header("Location: order_detail.php?id=" . $order_id);
            exit();
        }
    }
}

include 'inc_header.php';
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- Navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- End Of Navbar -->

    <!-- Sidebar -->
    <?php include 'inc_sidebar.php'; ?>
    <!-- End Of Sidebar -->

    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4 bg-white rounded-lg mt-14">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-semibold text-blue-800">Detail Pesanan #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></h2>
                    <p class="text-blue-600">Tanggal: <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></p>
                </div>
                <div class="flex gap-2">
                    <a href="transactions.php" class="gap-2 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <span class="material-symbols-rounded">arrow_back</span>
                        Kembali
                    </a>
                    <a href="print_invoice.php?id=<?php echo $order['order_id']; ?>" target="_blank"
                        class="gap-2 inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <span class="material-symbols-rounded">print</span>
                        Cetak Invoice
                    </a>
                </div>
            </div>

            <!-- Status dan Update Form -->
            <div class="rounded-lg bg-blue-50 p-6 mb-6 border border-blue-200">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-600">Status Pesanan</h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                            <?php
                            switch ($order['status']) {
                                case 'pending':
                                    echo 'bg-yellow-100 text-yellow-800';
                                    break;
                                case 'paid':
                                    echo 'bg-green-100 text-green-800';
                                    break;
                                case 'processing':
                                    echo 'bg-blue-100 text-blue-800';
                                    break;
                                case 'shipped':
                                    echo 'bg-purple-100 text-purple-800';
                                    break;
                                case 'delivered':
                                    echo 'bg-green-100 text-green-800';
                                    break;
                                case 'cancelled':
                                    echo 'bg-red-100 text-red-800';
                                    break;
                                default:
                                    echo 'bg-blue-100 text-blue-800';
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
                    <form method="POST" class="flex flex-wrap gap-4 items-center ">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-blue-600 mb-1">Status Pesanan</label>
                            <select name="status" class="w-full rounded-lg border-blue-300 border border-blue-300 focus:border-blue-500 focus:ring-blue-500 py-2.5 px-4 bg-white">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                <option value="paid" <?php echo $order['status'] === 'paid' ? 'selected' : ''; ?>>Sudah Dibayar</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Sedang Diproses</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Dalam Pengiriman</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Selesai</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-blue-600 mb-1">Ekspedisi</label>
                            <input type="text" name="courier" placeholder="Masukkan nama kurir"
                                value="<?php echo htmlspecialchars($order['courier'] ?? ''); ?>"
                                class="w-full rounded-lg border border-blue-300 focus:border-blue-500 focus:ring-blue-500 py-2.5 px-4 bg-white">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-blue-600 mb-1">Nomor Resi</label>
                            <input type="text" name="tracking_number" placeholder="Masukkan nomor resi"
                                value="<?php echo htmlspecialchars($order['tracking_number'] ?? ''); ?>"
                                class="w-full border rounded-lg border-blue-300 focus:border-blue-500 focus:ring-blue-500 py-2.5 px-4 bg-white">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="update_status"
                                class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center gap-2">
                                <span class="material-symbols-rounded">
                                    published_with_changes
                                </span>
                                Update Status
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Informasi Pelanggan -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="rounded-lg p-6 bg-blue-50 border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-600 mb-4">Informasi Pelanggan</h3>
                    <div class="space-y-3">
                        <p class="flex items-center gap-2">
                            <span class="font-medium text-blue-700 min-w-[100px]">Nama</span>
                            <span class="text-blue-900">: <?php echo htmlspecialchars($order['customer_name']); ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="font-medium text-blue-700 min-w-[100px]">Email</span>
                            <span class="text-blue-900">: <?php echo htmlspecialchars($order['customer_email']); ?></span>
                        </p>
                    </div>
                </div>

                <div class="rounded-lg p-6 bg-blue-50 border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-600 mb-4">Alamat Pengiriman</h3>
                    <div class="space-y-3">
                        <p class="flex items-center gap-2">
                            <span class="font-medium text-blue-700 min-w-[100px]">Nama Penerima</span>
                            <span class="text-blue-900">: <?php echo htmlspecialchars($order['recipient_name']); ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="font-medium text-blue-700 min-w-[100px]">Telepon</span>
                            <span class="text-blue-900">: <?php echo htmlspecialchars($order['phone']); ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="font-medium text-blue-700 min-w-[100px]">Provinsi</span>
                            <span class="text-blue-900">: <?php echo htmlspecialchars($order['province_name']); ?></span>
                        </p>
                        <p class="flex items-center gap-2">
                            <span class="font-medium text-blue-700 min-w-[100px]">Kota</span>
                            <span class="text-blue-900">: <?php echo htmlspecialchars($order['city_name']); ?></span>
                        </p>
                        <p class="flex items-start gap-2">
                            <span class="font-medium text-blue-700 min-w-[100px]">Alamat</span>
                            <span class="text-blue-900">: <?php echo htmlspecialchars($order['address']); ?></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Detail Items -->
            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-800 mb-4">Detail Items</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-blue-500">
                        <thead class="text-xs text-white uppercase bg-blue-600">
                            <tr>
                                <th class="px-6 py-3">Produk</th>
                                <th class="px-6 py-3">Harga</th>
                                <th class="px-6 py-3">Jumlah</th>
                                <th class="px-6 py-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr class="bg-white border-b border-blue-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <img src="<?php echo '../' . htmlspecialchars($item['image_url'] ?? 'img/no-image.jpg'); ?>"
                                                alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                class="w-16 h-16 object-cover rounded mr-4">
                                            <div>
                                                <p class="font-medium text-blue-900"><?php echo htmlspecialchars($item['product_name']); ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        Rp <?php echo number_format($item['price'], 0, ',', '.'); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php echo $item['quantity']; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="font-semibold text-blue-900">
                                <td colspan="3" class="px-6 py-4 text-right">Total:</td>
                                <td class="px-6 py-4">
                                    Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>