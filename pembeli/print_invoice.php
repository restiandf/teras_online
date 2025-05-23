<?php
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Cek apakah ada ID pesanan
if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

include '../koneksi.php';

// Ambil detail pesanan
$order_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_items
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id
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
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></title>
    <link rel="icon" href="../img/icon.png" type="image/x-icon" />
    <link
        href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css"
        rel="stylesheet" />
    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                padding: 0 !important;
                margin: 0 !important;
            }

            .max-w-4xl {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-lg shadow-sm">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <img src="../img/icon.png" alt="Logo" class="w-1/6 mr-4">
                <div>
                    <h1 class="text-3xl font-bold text-blue-600">INVOICE</h1>
                    <p class="text-gray-600">Teras Online Shop</p>
                </div>
            </div>
            <div class="border-t border-b border-gray-200 py-4">
                <p class="text-sm text-gray-500">Jl. Kemenangan, Jakarta</p>
                <p class="text-sm text-gray-500">Telp: +62 123 4567 890 | Email: info@terasonline.com</p>
            </div>
        </div>

        <!-- Invoice Info -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Invoice</h2>
                <div class="space-y-2">
                    <p class="text-sm">
                        <span class="font-medium text-gray-600">No. Invoice:</span>
                        <span class="text-gray-800">#<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium text-gray-600">Tanggal:</span>
                        <span class="text-gray-800"><?php echo date('d F Y H:i', strtotime($order['created_at'])); ?></span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium text-gray-600">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
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
                    </p>
                </div>
            </div>
            <div>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pelanggan</h2>
                <div class="space-y-2">
                    <p class="text-sm">
                        <span class="font-medium text-gray-600">Nama:</span>
                        <span class="text-gray-800"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium text-gray-600">Email:</span>
                        <span class="text-gray-800"><?php echo htmlspecialchars($order['customer_email']); ?></span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium text-gray-600">Alamat:</span>
                        <span class="text-gray-800"><?php echo htmlspecialchars($order['province_name']); ?>, <?php echo htmlspecialchars($order['city_name']); ?>, <?php echo htmlspecialchars($order['address']); ?></span>
                    </p>
                    <p class="text-sm">
                        <span class="font-medium text-gray-600">Telepon:</span>
                        <span class="text-gray-800"><?php echo htmlspecialchars($order['phone']); ?></span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Detail Item</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($order_items as $index => $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo $item['quantity']; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Total Section -->
        <div class="flex justify-end">
            <div class="w-64">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-900">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Biaya Pengiriman</span>
                        <span class="text-gray-900">Rp <?php echo number_format($order['shipping_cost'], 0, ',', '.'); ?></span>
                    </div>
                    <div class="border-t pt-2 mt-2">
                        <div class="flex justify-between font-semibold">
                            <span class="text-gray-800">Total</span>
                            <span class="text-blue-600">Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-12 pt-8 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-600">Terima kasih telah berbelanja di Teras Online Shop</p>
            <p class="text-xs text-gray-500 mt-1">Invoice ini adalah bukti pembayaran yang sah</p>
        </div>
        <div class="no-print flex justify-center mb-8 mt-3">
            <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors flex items-center">
                <span class="material-symbols-rounded text-sm mr-2">print</span>
                Cetak Invoice
            </button>
        </div>

    </div>

</body>

</html>