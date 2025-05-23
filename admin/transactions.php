<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Query dasar
$sql = "SELECT o.*, u.name as customer_name, u.email as customer_email,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as total_items
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id";

// Tambahkan filter status jika bukan 'all'
if ($status_filter !== 'all') {
    $sql .= " WHERE o.status = ?";
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($status_filter !== 'all') {
    mysqli_stmt_bind_param($stmt, "s", $status_filter);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

include 'inc_header.php';
?>

<body class="bg-gray-100" style="font-family: 'Inter', sans-serif">
    <!-- Navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- End Of Navbar -->

    <!-- Sidebar -->
    <?php include 'inc_sidebar.php'; ?>
    <!-- End Of Sidebar -->

    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4 border border-gray-200 rounded-lg mt-14 bg-white">
            <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-4">
                <h2 class="text-2xl font-extrabold text-blue-600 flex items-center gap-2 "><span class="material-symbols-rounded">receipt_long</span> Daftar Transaksi</h2>
                <div class="flex gap-2">
                    <a href="?status=all" class="px-4 py-2 rounded-lg <?php echo $status_filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Semua
                    </a>
                    <a href="?status=pending" class="px-4 py-2 rounded-lg <?php echo $status_filter === 'pending' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Pending
                    </a>
                    <a href="?status=paid" class="px-4 py-2 rounded-lg <?php echo $status_filter === 'paid' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Dibayar
                    </a>
                    <a href="?status=processing" class="px-4 py-2 rounded-lg <?php echo $status_filter === 'processing' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Diproses
                    </a>
                    <a href="?status=shipped" class="px-4 py-2 rounded-lg <?php echo $status_filter === 'shipped' ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Dikirim
                    </a>
                    <a href="?status=delivered" class="px-4 py-2 rounded-lg <?php echo $status_filter === 'delivered' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Selesai
                    </a>
                    <a href="?status=cancelled" class="px-4 py-2 rounded-lg <?php echo $status_filter === 'cancelled' ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        Dibatalkan
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500" id="search-table">
                    <thead class="text-xs text-blue-600 uppercase bg-blue-50 border-b border-blue-200">
                        <tr>
                            <th class="px-6 py-3">No</th>
                            <th class="px-6 py-3">ID</th>
                            <th class="px-6 py-3">Tanggal</th>
                            <th class="px-6 py-3">Pelanggan</th>
                            <th class="px-6 py-3">Total Item</th>
                            <th class="px-6 py-3">Total</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $nomor = 1; ?>
                        <?php foreach ($orders as $order): ?>
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <td class="px-6 py-4"><?php echo $nomor++; ?></td>
                                <td class="px-6 py-4">
                                    #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-gray-900"><?php echo htmlspecialchars($order['customer_name']); ?></p>
                                        <p class="text-gray-500"><?php echo htmlspecialchars($order['customer_email']); ?></p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo $order['total_items']; ?> item
                                </td>
                                <td class="px-6 py-4">
                                    Rp <?php echo number_format($order['grand_total'], 0, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-4">
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
                                </td>
                                <td class="px-6 py-4">
                                    <a href="order_detail.php?id=<?php echo $order['order_id']; ?>" class="text-blue-600 hover:text-blue-900">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- DataTables JS -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#search-table').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                    infoEmpty: "Tidak ada data yang tersedia",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    }
                },
                dom: '<"flex justify-between items-center mb-4"<"flex items-center gap-2"l><"flex items-center gap-2"f>>rtip',
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Semua"]
                ],
                pageLength: 10
            });
        });
    </script>
</body>

</html>