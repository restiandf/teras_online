<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil data rating
$sql = "SELECT r.*, p.name as product_name, u.name as user_name 
        FROM ratings r 
        JOIN products p ON r.product_id = p.product_id 
        JOIN users u ON r.user_id = u.user_id 
        ORDER BY r.created_at DESC";
$result = mysqli_query($conn, $sql);

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
        <div class="rounded-lg mt-14 bg-white p-4">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-semibold text-gray-900">Daftar Rating</h1>
                <a href="add_rates.php" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center gap-2">
                    <span class="material-symbols-rounded">add</span>
                    Tambah Rating
                </a>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th class="px-6 py-3">Produk</th>
                                <th class="px-6 py-3">Pengguna</th>
                                <th class="px-6 py-3">Rating</th>
                                <th class="px-6 py-3">Komentar</th>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['user_name']); ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <span class="material-symbols-rounded text-<?php echo $i <= $row['rating'] ? 'amber-400' : 'gray-300'; ?>">
                                                    star
                                                </span>
                                            <?php endfor; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['comment']); ?></td>
                                    <td class="px-6 py-4"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="view_rates.php?id=<?php echo $row['rating_id']; ?>"
                                                class="text-blue-600 hover:text-blue-800">
                                                <span class="material-symbols-rounded">visibility</span>
                                            </a>
                                            <a href="edit_rates.php?id=<?php echo $row['rating_id']; ?>"
                                                class="text-amber-600 hover:text-amber-800">
                                                <span class="material-symbols-rounded">edit</span>
                                            </a>
                                            <button onclick="deleteRating(<?php echo $row['rating_id']; ?>)"
                                                class="text-red-600 hover:text-red-800">
                                                <span class="material-symbols-rounded">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        function deleteRating(id) {
            if (confirm('Apakah Anda yakin ingin menghapus rating ini?')) {
                window.location.href = 'delete_rates.php?id=' + id;
            }
        }
    </script>
</body>

</html>