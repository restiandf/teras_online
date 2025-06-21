<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil ID rating dari URL
$rating_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data rating
$sql = "SELECT r.*, p.name as product_name, u.name as user_name 
        FROM ratings r 
        JOIN products p ON r.product_id = p.product_id 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.rating_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $rating_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rating = mysqli_fetch_assoc($result);

// Jika rating tidak ditemukan
if (!$rating) {
    header("Location: rates.php");
    exit();
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
        <div class="rounded-lg mt-14">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold text-gray-900">Detail Rating</h1>
                <a href="rates.php" class="text-blue-600 hover:text-blue-800 flex items-center gap-2">
                    <span class="material-symbols-rounded">arrow_back</span>
                    Kembali
                </a>
            </div>

            <!-- Detail Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Rating</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Produk</label>
                                <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($rating['product_name']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Pengguna</label>
                                <p class="mt-1 text-gray-900"><?php echo htmlspecialchars($rating['user_name']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Rating</label>
                                <div class="mt-1 flex items-center">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="material-symbols-rounded text-<?php echo $i <= $rating['rating'] ? 'amber-400' : 'gray-300'; ?>">
                                            star
                                        </span>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Komentar</label>
                                <p class="mt-1 text-gray-900"><?php echo nl2br(htmlspecialchars($rating['comment'])); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                                <p class="mt-1 text-gray-900"><?php echo date('d/m/Y H:i', strtotime($rating['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>