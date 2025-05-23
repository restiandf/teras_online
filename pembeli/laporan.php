<?php
include '../session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil data pesan dari user yang login
$sql = "SELECT m.*, 
        (SELECT COUNT(*) FROM message_replies WHERE message_id = m.message_id) as reply_count 
        FROM messages m 
        WHERE m.user_id = ? 
        ORDER BY m.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$messages = mysqli_fetch_all($result, MYSQLI_ASSOC);

include 'inc_header.php';
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- Navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- End Of Navbar -->

    <!-- Main Content -->
    <div class="p-4">
        <div class="max-w-7xl mx-auto mt-16">
            <!-- Header Section -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-2xl font-bold text-blue-600 flex items-center gap-2">
                            <span class="material-symbols-rounded">contact_support</span> Laporan Saya
                        </h2>
                        <p class="text-gray-600 mt-1">Kelola dan pantau status laporan Anda</p>
                    </div>
                    <button type="button" data-modal-target="addReportModal" data-modal-toggle="addReportModal" class="flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 transition-all duration-200">
                        <span class="material-symbols-rounded mr-2">add</span>Buat Laporan Baru
                    </button>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="p-4 mb-6 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
                    <div class="flex items-center">
                        <span class="material-symbols-rounded mr-2">check_circle</span>
                        <?php
                        $success = $_GET['success'];
                        if ($success == 'sent') echo "Laporan berhasil dikirim!";
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
                    <div class="flex items-center">
                        <span class="material-symbols-rounded mr-2">error</span>
                        <?php
                        $error = $_GET['error'];
                        if ($error == 'empty') echo "Semua field harus diisi!";
                        elseif ($error == 'dberror') echo "Terjadi kesalahan database!";
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Laporan</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo count($messages); ?></p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-full">
                            <span class="material-symbols-rounded text-blue-600">description</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Belum Dibaca</p>
                            <p class="text-2xl font-bold text-yellow-600">
                                <?php echo count(array_filter($messages, function ($m) {
                                    return $m['status'] == 'unread';
                                })); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-yellow-100 rounded-full">
                            <span class="material-symbols-rounded text-yellow-600">mail</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Balasan</p>
                            <p class="text-2xl font-bold text-green-600">
                                <?php echo array_sum(array_column($messages, 'reply_count')); ?>
                            </p>
                        </div>
                        <div class="p-3 bg-green-100 rounded-full">
                            <span class="material-symbols-rounded text-green-600">reply</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Laporan -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Laporan</h3>
                </div>
                <div class="p-6">
                    <table class="w-full text-sm text-left text-gray-500" id="search-table">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">No</th>
                                <th scope="col" class="px-6 py-3">Subjek</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Tanggal</th>
                                <th scope="col" class="px-6 py-3">Balasan</th>
                                <th scope="col" class="px-6 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nomor = 1;
                            foreach ($messages as $row): ?>
                                <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-200 <?php echo $row['status'] == 'unread' ? 'font-semibold' : ''; ?>">
                                    <td class="px-6 py-4 text-center"><?php echo $nomor++; ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="bg-<?php echo $row['status'] == 'unread' ? 'yellow' : 'green'; ?>-100 text-<?php echo $row['status'] == 'unread' ? 'yellow' : 'green'; ?>-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                            <?php echo $row['status'] == 'unread' ? 'Belum Dibaca' : 'Sudah Dibaca'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['reply_count'] > 0): ?>
                                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                <?php echo $row['reply_count']; ?> Balasan
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                                Belum ada balasan
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button type="button" data-modal-target="viewReportModal<?php echo $row['message_id']; ?>" data-modal-toggle="viewReportModal<?php echo $row['message_id']; ?>" class="font-medium text-blue-600 hover:text-blue-800 transition-colors duration-200">
                                            <span class="material-symbols-rounded text-sm mr-1">visibility</span>Lihat Detail
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal Lihat Laporan -->
                                <div id="viewReportModal<?php echo $row['message_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                    <div class="relative w-full max-w-lg max-h-full">
                                        <div class="relative bg-white rounded-lg shadow">
                                            <div class="flex items-start justify-between p-3 border-b rounded-t">
                                                <h3 class="text-lg font-semibold text-gray-900">
                                                    Detail Laporan
                                                </h3>
                                                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="viewReportModal<?php echo $row['message_id']; ?>">
                                                    <span class="material-symbols-rounded">close</span>
                                                </button>
                                            </div>
                                            <div class="p-4 space-y-4">
                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="col-span-2">
                                                        <label class="block mb-1 text-sm font-medium text-gray-900">Subjek</label>
                                                        <p class="text-sm text-gray-700"><?php echo htmlspecialchars($row['subject']); ?></p>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="block mb-1 text-sm font-medium text-gray-900">Laporan</label>
                                                        <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($row['message']); ?></p>
                                                    </div>
                                                    <div>
                                                        <label class="block mb-1 text-sm font-medium text-gray-900">Tanggal</label>
                                                        <p class="text-sm text-gray-700"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></p>
                                                    </div>
                                                    <div>
                                                        <label class="block mb-1 text-sm font-medium text-gray-900">Status</label>
                                                        <p class="text-sm text-gray-700"><?php echo $row['status'] == 'unread' ? 'Belum Dibaca' : 'Sudah Dibaca'; ?></p>
                                                    </div>

                                                    <!-- Riwayat Balasan -->
                                                    <?php
                                                    $sql_replies = "SELECT r.*, u.name as admin_name 
                                                                 FROM message_replies r 
                                                                 JOIN users u ON r.admin_id = u.user_id 
                                                                 WHERE r.message_id = ? 
                                                                 ORDER BY r.created_at ASC";
                                                    $stmt_replies = mysqli_prepare($conn, $sql_replies);
                                                    mysqli_stmt_bind_param($stmt_replies, "i", $row['message_id']);
                                                    mysqli_stmt_execute($stmt_replies);
                                                    $result_replies = mysqli_stmt_get_result($stmt_replies);

                                                    if (mysqli_num_rows($result_replies) > 0): ?>
                                                        <div class="col-span-2 mt-2">
                                                            <label class="block mb-1 text-sm font-medium text-gray-900">Riwayat Balasan</label>
                                                            <div class="space-y-2">
                                                                <?php while ($reply = mysqli_fetch_assoc($result_replies)): ?>
                                                                    <div class="bg-gray-50 p-3 rounded-lg">
                                                                        <div class="flex justify-between items-start mb-1">
                                                                            <div>
                                                                                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($reply['admin_name']); ?></p>
                                                                                <p class="text-xs text-gray-500"><?php echo date('d/m/Y H:i', strtotime($reply['created_at'])); ?></p>
                                                                            </div>
                                                                        </div>
                                                                        <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($reply['reply']); ?></p>
                                                                    </div>
                                                                <?php endwhile; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="flex items-center p-3 space-x-2 border-t border-gray-200 rounded-b">
                                                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-4 py-2 hover:text-gray-900 focus:z-10" data-modal-hide="viewReportModal<?php echo $row['message_id']; ?>">
                                                    Tutup
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Laporan -->
    <div id="addReportModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Buat Laporan Baru
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="addReportModal">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <form action="send_report.php" method="POST">
                    <div class="p-6 space-y-6">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Subjek</label>
                            <input type="text" name="subject" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Laporan</label>
                            <textarea name="message" rows="6" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required></textarea>
                        </div>
                    </div>
                    <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Kirim Laporan
                        </button>
                        <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="addReportModal">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include('../inc_footer.php'); ?>

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
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4 gap-4"<"flex items-center gap-2"l><"flex items-center gap-2"f>>rtip',
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Semua"]
                ],
                pageLength: 10,
                order: [
                    [3, 'desc']
                ],
                columnDefs: [{
                    className: "px-6",
                    targets: "_all"
                }]
            });
        });
    </script>

</body>

</html>