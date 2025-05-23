<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil data pesan
$sql = "SELECT m.*, u.name as user_name, u.email as user_email 
        FROM messages m 
        LEFT JOIN users u ON m.user_id = u.user_id 
        ORDER BY m.created_at DESC";
$result = mysqli_query($conn, $sql);

include 'inc_header.php';
?>

<body class="bg-gray-100" style="font-family: 'Inter', sans-serif">
    <!-- Navbar -->
    <?php
    include 'inc_navbar.php';
    ?>
    <!-- End Of Navbar -->

    <!-- Sidebar -->
    <?php
    include 'inc_sidebar.php';
    ?>
    <!-- End Of Sidebar -->

    <!-- Main Content -->
    <div class="p-4 sm:ml-64">
        <div class="p-4 mt-14 border border-gray-200 rounded-lg bg-white">
            <!-- Alert Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                    <?php
                    $success = $_GET['success'];
                    if ($success == 'read') echo "Pesan ditandai sebagai telah dibaca!";
                    elseif ($success == 'deleted') echo "Pesan berhasil dihapus!";
                    elseif ($success == 'replied') echo "Balasan berhasil dikirim!";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                    <?php
                    $error = $_GET['error'];
                    if ($error == 'dberror') echo "Terjadi kesalahan database!";
                    elseif ($error == 'invalid') echo "ID pesan tidak valid!";
                    elseif ($error == 'empty') echo "Balasan tidak boleh kosong!";
                    elseif ($error == 'mailerror') echo "Gagal mengirim email balasan!";
                    ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-4">
                <h2 class="text-2xl font-extrabold text-blue-600 flex items-center gap-2"><span class="material-symbols-rounded">mail</span> Kelola Pesan</h2>
            </div>

            <!-- Tabel Pesan -->
            <div class="relative overflow-x-auto p-3">
                <table class="w-full text-sm text-left text-gray-500" id="search-table">
                    <thead class="text-xs text-blue-600 uppercase bg-blue-50 border-b border-blue-200">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <th scope="col" class="px-6 py-3">Pengirim</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Subjek</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Tanggal</th>
                            <th scope="col" class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="bg-white border-b <?php echo $row['status'] == 'unread' ? 'font-semibold' : ''; ?>">
                                <td class="px-6 py-4 text-center"><?php echo $nomor++; ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-<?php echo $row['status'] == 'unread' ? 'yellow' : 'green'; ?>-100 text-<?php echo $row['status'] == 'unread' ? 'yellow' : 'green'; ?>-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        <?php echo $row['status'] == 'unread' ? 'Belum Dibaca' : 'Sudah Dibaca'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                                <td class="px-6 py-4">
                                    <button type="button" data-modal-target="viewMessageModal<?php echo $row['message_id']; ?>" data-modal-toggle="viewMessageModal<?php echo $row['message_id']; ?>" class="font-medium text-blue-600 hover:underline mr-3">
                                        Lihat
                                    </button>
                                    <button type="button" data-modal-target="deleteMessageModal<?php echo $row['message_id']; ?>" data-modal-toggle="deleteMessageModal<?php echo $row['message_id']; ?>" class="font-medium text-red-600 hover:underline">
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Lihat Pesan -->
                            <div id="viewMessageModal<?php echo $row['message_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                <div class="relative w-full max-w-2xl max-h-full">
                                    <div class="relative bg-white rounded-lg shadow">
                                        <div class="flex items-start justify-between p-4 border-b rounded-t">
                                            <h3 class="text-xl font-semibold text-gray-900">
                                                Detail Pesan
                                            </h3>
                                            <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="viewMessageModal<?php echo $row['message_id']; ?>">
                                                <span class="material-symbols-rounded">close</span>
                                            </button>
                                        </div>
                                        <div class="p-6 space-y-6">
                                            <div class="grid grid-cols-2 gap-6">
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Pengirim</label>
                                                    <p class="text-gray-700"><?php echo htmlspecialchars($row['name']); ?></p>
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                                                    <p class="text-gray-700"><?php echo htmlspecialchars($row['email']); ?></p>
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Subjek</label>
                                                    <p class="text-gray-700"><?php echo htmlspecialchars($row['subject']); ?></p>
                                                </div>
                                                <div class="col-span-2">
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Pesan</label>
                                                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($row['message']); ?></p>
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Tanggal</label>
                                                    <p class="text-gray-700"><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></p>
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Status</label>
                                                    <p class="text-gray-700"><?php echo $row['status'] == 'unread' ? 'Belum Dibaca' : 'Sudah Dibaca'; ?></p>
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
                                                    <div class="col-span-2 mt-4">
                                                        <label class="block mb-2 text-sm font-medium text-gray-900">Riwayat Balasan</label>
                                                        <div class="space-y-4">
                                                            <?php while ($reply = mysqli_fetch_assoc($result_replies)): ?>
                                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                                    <div class="flex justify-between items-start mb-2">
                                                                        <div>
                                                                            <p class="font-medium text-gray-900"><?php echo htmlspecialchars($reply['admin_name']); ?></p>
                                                                            <p class="text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($reply['created_at'])); ?></p>
                                                                        </div>
                                                                    </div>
                                                                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($reply['reply']); ?></p>
                                                                </div>
                                                            <?php endwhile; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                                            <?php if ($row['status'] == 'unread'): ?>
                                                <a href="mark_as_read.php?id=<?php echo $row['message_id']; ?>" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                                    Tandai Sudah Dibaca
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center" data-modal-target="replyMessageModal<?php echo $row['message_id']; ?>" data-modal-toggle="replyMessageModal<?php echo $row['message_id']; ?>">
                                                Balas
                                            </button>
                                            <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="viewMessageModal<?php echo $row['message_id']; ?>">
                                                Tutup
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus Pesan -->
                            <div id="deleteMessageModal<?php echo $row['message_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                <div class="relative w-full max-w-md max-h-full">
                                    <div class="relative bg-white rounded-lg shadow">
                                        <div class="p-6 text-center">
                                            <span class="material-symbols-rounded text-red-500 text-5xl mb-4">warning</span>
                                            <h3 class="mb-5 text-lg font-normal text-gray-500">Apakah Anda yakin ingin menghapus pesan ini?</h3>
                                            <div class="flex justify-center gap-4">
                                                <a href="delete_message.php?id=<?php echo $row['message_id']; ?>" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5">
                                                    Ya, Hapus
                                                </a>
                                                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="deleteMessageModal<?php echo $row['message_id']; ?>">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Balas Pesan -->
                            <div id="replyMessageModal<?php echo $row['message_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                <div class="relative w-full max-w-2xl max-h-full">
                                    <div class="relative bg-white rounded-lg shadow">
                                        <div class="flex items-start justify-between p-4 border-b rounded-t">
                                            <h3 class="text-xl font-semibold text-gray-900">
                                                Balas Pesan
                                            </h3>
                                            <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="replyMessageModal<?php echo $row['message_id']; ?>">
                                                <span class="material-symbols-rounded">close</span>
                                            </button>
                                        </div>
                                        <form action="reply_message.php" method="POST">
                                            <input type="hidden" name="message_id" value="<?php echo $row['message_id']; ?>">
                                            <input type="hidden" name="user_email" value="<?php echo htmlspecialchars($row['email']); ?>">
                                            <input type="hidden" name="subject" value="<?php echo htmlspecialchars($row['subject']); ?>">
                                            <div class="p-6 space-y-6">
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Kepada</label>
                                                    <p class="text-gray-700"><?php echo htmlspecialchars($row['name']); ?> (<?php echo htmlspecialchars($row['email']); ?>)</p>
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Subjek</label>
                                                    <p class="text-gray-700">Re: <?php echo htmlspecialchars($row['subject']); ?></p>
                                                </div>
                                                <div>
                                                    <label class="block mb-2 text-sm font-medium text-gray-900">Balasan</label>
                                                    <textarea name="reply" rows="6" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required></textarea>
                                                </div>
                                            </div>
                                            <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                                                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                                    Kirim Balasan
                                                </button>
                                                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="replyMessageModal<?php echo $row['message_id']; ?>">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
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
                dom: '<"flex flex-col sm:flex-row justify-between items-center mb-4 gap-4"<"flex items-center gap-2"l><"flex items-center gap-2"f>>rtip',
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Semua"]
                ],
                pageLength: 10,
                order: [
                    [5, 'desc']
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