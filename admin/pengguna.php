<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Ambil data pengguna
$sql = "SELECT * FROM users ORDER BY user_id DESC";
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
                    if ($success == 'added') echo "Pengguna berhasil ditambahkan!";
                    elseif ($success == 'updated') echo "Pengguna berhasil diperbarui!";
                    elseif ($success == 'deleted') echo "Pengguna berhasil dihapus!";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                    <?php
                    $error = $_GET['error'];
                    if ($error == 'dberror') echo "Terjadi kesalahan database!";
                    elseif ($error == 'email') echo "Email sudah terdaftar!";
                    elseif ($error == 'password') echo "Password tidak cocok!";
                    ?>
                </div>
            <?php endif; ?>

            <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-4">
                <h2 class="text-2xl font-extrabold text-blue-600 flex items-center gap-2"><span class="material-symbols-rounded">group</span> Kelola Pengguna</h2>
                <button type="button" data-modal-target="addUserModal" data-modal-toggle="addUserModal" class="flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                    <span class="material-symbols-rounded">add</span>Tambah Pengguna
                </button>
            </div>

            <!-- Tabel Pengguna -->
            <div class="relative overflow-x-auto p-3">
                <table class="w-full text-sm text-left text-gray-500" id="search-table">
                    <thead class="text-xs text-blue-600 uppercase bg-blue-50 border-b border-blue-200">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <!-- <th scope="col" class="px-6 py-3">ID</th> -->
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Role</th>
                            <th scope="col" class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nomor = 1;
                        while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="bg-white border-b">
                                <td class="px-6 py-4 text-center"><?php echo $nomor++; ?></td>
                                <!-- <td class="px-6 py-4"><?php echo $row['user_id']; ?></td> -->
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['name']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        <?php echo ucfirst($row['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <button type="button" data-modal-target="editUserModal<?php echo $row['user_id']; ?>" data-modal-toggle="editUserModal<?php echo $row['user_id']; ?>" class="font-medium text-blue-600 hover:underline mr-3">
                                        Edit
                                    </button>
                                    <button type="button" data-modal-target="deleteUserModal<?php echo $row['user_id']; ?>" data-modal-toggle="deleteUserModal<?php echo $row['user_id']; ?>" class="font-medium text-red-600 hover:underline">
                                        Hapus
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Edit Pengguna -->
                            <div id="editUserModal<?php echo $row['user_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                <div class="relative w-full max-w-2xl max-h-full">
                                    <div class="relative bg-white rounded-lg shadow">
                                        <div class="flex items-start justify-between p-4 border-b rounded-t">
                                            <h3 class="text-xl font-semibold text-gray-900">
                                                Edit Pengguna
                                            </h3>
                                            <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="editUserModal<?php echo $row['user_id']; ?>">
                                                <span class="material-symbols-rounded">close</span>
                                            </button>
                                        </div>
                                        <form action="edit_user.php" method="POST">
                                            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                            <div class="p-6 space-y-6">
                                                <div class="grid grid-cols-2 gap-6">
                                                    <div class="col-span-2">
                                                        <label class="block mb-2 text-sm font-medium text-gray-900">Nama</label>
                                                        <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                                                        <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                                    </div>
                                                    <div>
                                                        <label class="block mb-2 text-sm font-medium text-gray-900">Role</label>
                                                        <select name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                                            <option value="admin" <?php echo $row['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                            <option value="pembeli" <?php echo $row['role'] == 'pembeli' ? 'selected' : ''; ?>>Pembeli</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <label class="block mb-2 text-sm font-medium text-gray-900">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                                                        <input type="password" name="new_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                                                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                                    Simpan
                                                </button>
                                                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="editUserModal<?php echo $row['user_id']; ?>">
                                                    Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Hapus Pengguna -->
                            <div id="deleteUserModal<?php echo $row['user_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                                <div class="relative w-full max-w-md max-h-full">
                                    <div class="relative bg-white rounded-lg shadow">
                                        <div class="p-6 text-center">
                                            <span class="material-symbols-rounded text-red-500 text-5xl mb-4">warning</span>
                                            <h3 class="mb-5 text-lg font-normal text-gray-500">Apakah Anda yakin ingin menghapus pengguna ini?</h3>
                                            <div class="flex justify-center gap-4">
                                                <a href="delete_user.php?id=<?php echo $row['user_id']; ?>" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5">
                                                    Ya, Hapus
                                                </a>
                                                <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="deleteUserModal<?php echo $row['user_id']; ?>">
                                                    Batal
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Pengguna -->
    <div id="addUserModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Tambah Pengguna Baru
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="addUserModal">
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>
                <form action="add_user.php" method="POST">
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <div class="col-span-2">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Nama</label>
                                <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            </div>
                            <div class="col-span-2">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                                <input type="email" name="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Password</label>
                                <input type="password" name="password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            </div>
                            <div>
                                <label class="block mb-2 text-sm font-medium text-gray-900">Role</label>
                                <select name="role" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                    <option value="pembeli">Pembeli</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan
                        </button>
                        <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="addUserModal">
                            Batal
                        </button>
                    </div>
                </form>
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
                    [0, 'asc']
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