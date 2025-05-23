<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../koneksi.php';

// Ambil data produk
$sql = "SELECT p.*, pi.image_url 
        FROM products p 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        ORDER BY p.product_id DESC";
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
          if ($success == 'added') echo "Produk berhasil ditambahkan!";
          elseif ($success == 'updated') echo "Produk berhasil diperbarui!";
          elseif ($success == 'deleted') echo "Produk berhasil dihapus!";
          ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['error'])): ?>
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
          <?php
          $error = $_GET['error'];
          if ($error == 'dberror') echo "Terjadi kesalahan database!";
          elseif ($error == 'notimage') echo "File yang diupload bukan gambar!";
          elseif ($error == 'toobig') echo "Ukuran file terlalu besar!";
          elseif ($error == 'wrongformat') echo "Format file tidak didukung!";
          elseif ($error == 'uploadfailed') echo "Gagal mengupload file!";
          elseif ($error == 'invalid') echo "ID produk tidak valid!";
          ?>
        </div>
      <?php endif; ?>

      <div class="flex justify-between items-center mb-4 border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-extrabold text-blue-600 flex items-center gap-2"><span class="material-symbols-rounded">inventory_2</span> Kelola Produk</h2>
        <button type="button" data-modal-target="addProductModal" data-modal-toggle="addProductModal" class="flex items-center text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
          <span class="material-symbols-rounded">add</span>Tambah Produk
        </button>
      </div>

      <!-- Tabel Produk -->
      <div class="relative overflow-x-auto p-3">
        <table class="w-full text-sm text-left text-gray-500 " id="search-table">
          <thead class="text-xs text-blue-600 uppercase bg-blue-50 border-b border-blue-200">
            <tr>
              <th scope="col" class="px-6 py-3">No</th>
              <!-- <th scope="col" class="px-6 py-3">ID</th> -->
              <th scope="col" class="px-6 py-3">Gambar</th>
              <th scope="col" class="px-6 py-3">Nama Produk</th>
              <th scope="col" class="px-6 py-3">Harga</th>
              <th scope="col" class="px-6 py-3">Stok</th>
              <th scope="col" class="px-6 py-3">Status</th>
              <th scope="col" class="px-6 py-3">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $nomor = 1;
            while ($row = mysqli_fetch_assoc($result)): ?>
              <tr class="bg-white border-b">
                <td class="px-6 py-4 text-center"><?php echo $nomor++; ?></td>
                <!-- <td class="px-6 py-4"><?php echo $row['product_id']; ?></td> -->
                <td class="px-6 py-4">
                  <img src="../<?php echo $row['image_url']; ?>" class="w-16 h-16 object-cover rounded" alt="Product Image" />
                </td>
                <td class="px-6 py-4"><?php echo htmlspecialchars($row['name']); ?></td>
                <td class="px-6 py-4">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                <td class="px-6 py-4"><?php echo $row['stock']; ?></td>
                <td class="px-6 py-4">
                  <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                    <?php echo $row['stock'] > 0 ? 'Tersedia' : 'Habis'; ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <a href="view_product.php?id=<?php echo $row['product_id']; ?>" class="font-medium text-green-600 hover:underline mr-3">
                    Lihat
                  </a>
                  <button type="button" data-modal-target="editProductModal<?php echo $row['product_id']; ?>" data-modal-toggle="editProductModal<?php echo $row['product_id']; ?>" class="font-medium text-blue-600 hover:underline mr-3">
                    Edit
                  </button>
                  <button type="button" data-modal-target="deleteProductModal<?php echo $row['product_id']; ?>" data-modal-toggle="deleteProductModal<?php echo $row['product_id']; ?>" class="font-medium text-red-600 hover:underline">
                    Hapus
                  </button>
                </td>
              </tr>

              <!-- Modal Edit Produk -->
              <div id="editProductModal<?php echo $row['product_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-2xl max-h-full">
                  <div class="relative bg-white rounded-lg shadow">
                    <div class="flex items-start justify-between p-4 border-b rounded-t">
                      <h3 class="text-xl font-semibold text-gray-900">
                        Edit Produk
                      </h3>
                      <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="editProductModal<?php echo $row['product_id']; ?>">
                        <span class="material-symbols-rounded">close</span>
                      </button>
                    </div>
                    <form action="edit_product.php" method="POST" enctype="multipart/form-data">
                      <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                      <div class="p-6 space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                          <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Nama Produk</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                          </div>
                          <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Harga</label>
                            <input type="number" name="price" value="<?php echo $row['price']; ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                          </div>
                          <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Stok</label>
                            <input type="number" name="stock" value="<?php echo $row['stock']; ?>" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                          </div>
                          <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Deskripsi</label>
                            <textarea name="description" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required><?php echo htmlspecialchars($row['description']); ?></textarea>
                          </div>
                          <div class="col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Gambar Produk</label>
                            <div class="relative">
                              <input type="file" name="images[]" multiple
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    cursor-pointer bg-gray-50 border border-gray-300 rounded-lg focus:outline-none">
                              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="material-symbols-rounded text-gray-400">upload_file</span>
                              </div>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Pilih satu atau lebih gambar produk (Format: JPG, PNG, GIF. Maks: 2MB per gambar)</p>

                            <!-- Tampilkan gambar yang ada -->
                            <div class="mt-4">
                              <label class="block mb-2 text-sm font-medium text-gray-900">Gambar Saat Ini</label>
                              <div class="grid grid-cols-4 gap-4">
                                <?php
                                // Ambil semua gambar produk
                                $sql_images = "SELECT * FROM product_images WHERE product_id = ?";
                                $stmt_images = mysqli_prepare($conn, $sql_images);
                                mysqli_stmt_bind_param($stmt_images, "i", $row['product_id']);
                                mysqli_stmt_execute($stmt_images);
                                $result_images = mysqli_stmt_get_result($stmt_images);

                                while ($image = mysqli_fetch_assoc($result_images)) {
                                  echo '<div class="relative">';
                                  echo '<img src="../' . $image['image_url'] . '" class="w-full h-24 object-cover rounded" alt="Product Image">';
                                  if ($image['is_primary']) {
                                    echo '<span class="absolute top-1 right-1 bg-blue-500 text-white text-xs px-2 py-1 rounded">Utama</span>';
                                  }
                                  echo '</div>';
                                }
                                ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                          Simpan
                        </button>
                        <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="editProductModal<?php echo $row['product_id']; ?>">
                          Batal
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>

              <!-- Modal Hapus Produk -->
              <div id="deleteProductModal<?php echo $row['product_id']; ?>" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative w-full max-w-md max-h-full">
                  <div class="relative bg-white rounded-lg shadow">
                    <div class="p-6 text-center">
                      <span class="material-symbols-rounded text-red-500 text-5xl mb-4">warning</span>
                      <h3 class="mb-5 text-lg font-normal text-gray-500">Apakah Anda yakin ingin menghapus produk ini?</h3>
                      <div class="flex justify-center gap-4">
                        <a href="delete_product.php?id=<?php echo $row['product_id']; ?>" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5">
                          Ya, Hapus
                        </a>
                        <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="deleteProductModal<?php echo $row['product_id']; ?>">
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

  <!-- Modal Tambah Produk -->
  <div id="addProductModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-2xl max-h-full">
      <div class="relative bg-white rounded-lg shadow">
        <div class="flex items-start justify-between p-4 border-b rounded-t">
          <h3 class="text-xl font-semibold text-gray-900">
            Tambah Produk Baru
          </h3>
          <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="addProductModal">
            <span class="material-symbols-rounded">close</span>
          </button>
        </div>
        <form action="add_product.php" method="POST" enctype="multipart/form-data">
          <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-6">
              <div class="col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-900">Nama Produk</label>
                <input type="text" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
              </div>
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Harga</label>
                <input type="number" name="price" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
              </div>
              <div>
                <label class="block mb-2 text-sm font-medium text-gray-900">Stok</label>
                <input type="number" name="stock" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
              </div>
              <div class="col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-900">Deskripsi</label>
                <textarea name="description" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required></textarea>
              </div>
              <div class="col-span-2">
                <label class="block mb-2 text-sm font-medium text-gray-900">Gambar Produk</label>
                <div class="relative">
                  <input type="file" name="images[]" multiple
                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100
                        cursor-pointer bg-gray-50 border border-gray-300 rounded-lg focus:outline-none">
                  <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <span class="material-symbols-rounded text-gray-400">upload_file</span>
                  </div>
                </div>
                <p class="mt-1 text-sm text-gray-500">Pilih satu atau lebih gambar produk (Format: JPG, PNG, GIF. Maks: 2MB per gambar)</p>
              </div>
            </div>
          </div>
          <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
            <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
              Simpan
            </button>
            <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="addProductModal">
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