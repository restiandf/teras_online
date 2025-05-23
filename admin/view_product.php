<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../koneksi.php';

// Cek jika ada ID produk
if (!isset($_GET['id'])) {
    header("Location: products.php?error=noid");
    exit();
}

$product_id = $_GET['id'];

// Ambil data produk
$sql = "SELECT p.*, pi.image_url 
        FROM products p 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        WHERE p.product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

// Ambil semua gambar produk
$sql_images = "SELECT image_url FROM product_images WHERE product_id = ? ORDER BY is_primary DESC";
$stmt_images = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_images, "i", $product_id);
mysqli_stmt_execute($stmt_images);
$result_images = mysqli_stmt_get_result($stmt_images);
$images = [];
while ($img = mysqli_fetch_assoc($result_images)) {
    $images[] = $img['image_url'];
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
        <div class="p-4 border-2 border-gray-200 rounded-lg mt-14">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-900">Detail Produk</h2>
                <div class="flex gap-2">
                    <a href="products.php" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        Kembali
                    </a>
                    <button type="button" data-modal-target="editProductModal" data-modal-toggle="editProductModal" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5">
                        Edit Produk
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Carousel -->
                <div id="default-carousel" class="relative w-full" data-carousel="slide">
                    <!-- Carousel wrapper -->
                    <div class="relative h-96 overflow-hidden rounded-lg bg-gray-700">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="hidden duration-700 ease-in-out" data-carousel-item="<?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="../<?php echo $image; ?>" class="absolute block w-full h-full object-contain" alt="Product Image">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Slider controls -->
                    <button type="button" class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                            <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4" />
                            </svg>
                            <span class="sr-only">Previous</span>
                        </span>
                    </button>
                    <button type="button" class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50 group-focus:ring-4 group-focus:ring-white group-focus:outline-none">
                            <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4" />
                            </svg>
                            <span class="sr-only">Next</span>
                        </span>
                    </button>
                </div>

                <!-- Product Details -->
                <div class="p-4">
                    <h2 class="text-2xl font-bold mb-4">Detail Produk</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">ID Produk</label>
                            <p class="mt-1 text-gray-900"><?php echo $row['product_id']; ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
                            <p class="mt-1 text-gray-900"><?php echo $row['name']; ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Harga</label>
                            <p class="mt-1 text-gray-900">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stok</label>
                            <p class="mt-1 text-gray-900"><?php echo $row['stock']; ?> unit</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <p class="mt-1 text-gray-900"><?php echo $row['description']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div id="editProductModal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative w-full max-w-2xl max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-start justify-between p-4 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Edit Produk
                    </h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center" data-modal-hide="editProductModal">
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
                                <!-- Tampilkan gambar yang ada -->
                                <div class="grid grid-cols-3 gap-4 mb-4">
                                    <?php
                                    $sql_existing_images = "SELECT * FROM product_images WHERE product_id = ?";
                                    $stmt_existing = mysqli_prepare($conn, $sql_existing_images);
                                    mysqli_stmt_bind_param($stmt_existing, "i", $product_id);
                                    mysqli_stmt_execute($stmt_existing);
                                    $result_existing = mysqli_stmt_get_result($stmt_existing);
                                    while ($img = mysqli_fetch_assoc($result_existing)):
                                    ?>
                                        <div class="relative group" data-image-id="<?php echo $img['image_id']; ?>">
                                            <img src="../<?php echo $img['image_url']; ?>" class="w-full h-32 object-cover rounded-lg" alt="Product Image">
                                            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                                                <button type="button" onclick="deleteImage(<?php echo $img['image_id']; ?>)" class="text-white bg-red-600 hover:bg-red-700 p-2 rounded-full">
                                                    <span class="material-symbols-rounded">delete</span>
                                                </button>
                                            </div>
                                            <?php if ($img['is_primary']): ?>
                                                <span class="absolute top-2 left-2 bg-blue-600 text-white text-xs px-2 py-1 rounded">Primary</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <!-- Input untuk upload gambar baru -->
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
                        <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" data-modal-hide="editProductModal">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        function deleteImage(imageId) {
            if (confirm('Apakah Anda yakin ingin menghapus gambar ini?')) {
                fetch('delete_image.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'image_id=' + imageId
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Hapus elemen gambar dari DOM
                            const imageElement = document.querySelector(`[data-image-id="${imageId}"]`);
                            if (imageElement) {
                                imageElement.remove();
                            }
                            // Reload halaman untuk memperbarui tampilan
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal menghapus gambar');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Jika gambar sudah terhapus tapi ada error response, tetap reload halaman
                        window.location.reload();
                    });
            }
        }
    </script>
</body>

</html>