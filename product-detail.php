<?php
include 'inc_header.php';
include 'koneksi.php';

// Ambil ID produk dari URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Query untuk mengambil detail produk
$sql = "SELECT p.*, pi.image_url 
        FROM products p 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        WHERE p.product_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// Jika produk tidak ditemukan, redirect ke halaman utama
if (!$product) {
  header("Location: index.php");
  exit();
}

// Query untuk mengambil semua gambar produk
$sql_images = "SELECT * FROM product_images WHERE product_id = ?";
$stmt_images = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($stmt_images, "i", $product_id);
mysqli_stmt_execute($stmt_images);
$result_images = mysqli_stmt_get_result($stmt_images);
$images = mysqli_fetch_all($result_images, MYSQLI_ASSOC);
?>

<body>
  <?php
  include 'inc_navbar.php';
  ?>
  <section class="mb-8 pt-24">
    <div class="max-w-screen-xl mx-auto px-4">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-blue-50 border border-blue-200 rounded-lg relative">
          <!-- Gambar Produk -->
          <div id="product-carousel" class="relative w-full" data-carousel="slide">
            <!-- Carousel wrapper -->
            <div class="relative h-96 overflow-hidden rounded-lg">
              <?php foreach ($images as $index => $image): ?>
                <div class="hidden duration-700 ease-in-out flex items-center justify-center <?php echo $index === 0 ? 'active' : ''; ?>" data-carousel-item="<?php echo $index === 0 ? 'active' : ''; ?>">
                  <img src="<?php echo htmlspecialchars($image['image_url']); ?>" class="block object-cover rounded-lg p-8" alt="<?php echo htmlspecialchars($product['name']); ?>" />
                </div>
              <?php endforeach; ?>
            </div>
            <!-- Slider indicators -->
            <div class="absolute z-10 flex -translate-x-1/2 bottom-5 left-1/2 space-x-3 rtl:space-x-reverse">
              <?php foreach ($images as $index => $image): ?>
                <button type="button" class="w-3 h-3 rounded-full <?php echo $index === 0 ? 'bg-white' : 'bg-white/50'; ?>" aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $index + 1; ?>" data-carousel-slide-to="<?php echo $index; ?>"></button>
              <?php endforeach; ?>
            </div>
            <!-- Slider controls -->
            <button type="button" class="absolute top-0 start-0 z-[999] flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none pointer-events-auto" data-carousel-prev>
              <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4" />
                </svg>
                <span class="sr-only">Previous</span>
              </span>
            </button>
            <button type="button" class="absolute top-0 end-0 z-[999] flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none pointer-events-auto" data-carousel-next>
              <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                <svg class="w-4 h-4 text-white dark:text-gray-800 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4" />
                </svg>
                <span class="sr-only">Next</span>
              </span>
            </button>
          </div>
        </div>
        <div class="px-4">
          <!-- Detail Produk -->
          <div class="bg-white rounded-lg p-6 border border-gray-200">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($product['name']); ?></h1>

            <!-- Harga -->
            <div class="mb-6">
              <p class="text-2xl font-bold text-blue-600">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
            </div>

            <!-- Informasi Stok dan Penjualan -->
            <div class="grid grid-cols-2 gap-4 mb-6">
              <!-- Stok -->
              <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center space-x-2">
                  <span class="material-symbols-rounded text-blue-600">inventory_2</span>
                  <div>
                    <p class="text-sm text-gray-600">Stok Tersedia</p>
                    <p class="text-lg font-semibold text-blue-600"><?php echo $product['stock']; ?> pcs</p>
                  </div>
                </div>
              </div>

              <!-- Total Penjualan -->
              <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <div class="flex items-center space-x-2">
                  <span class="material-symbols-rounded text-green-600">trending_up</span>
                  <div>
                    <p class="text-sm text-gray-600">Total Terjual</p>
                    <p class="text-lg font-semibold text-green-600">
                      <?php
                      $sql_sales = "SELECT SUM(quantity) as total_sold 
                                  FROM order_items 
                                  WHERE product_id = ?";
                      $stmt = mysqli_prepare($conn, $sql_sales);
                      mysqli_stmt_bind_param($stmt, "i", $product_id);
                      mysqli_stmt_execute($stmt);
                      $result_sales = mysqli_stmt_get_result($stmt);
                      $sales = mysqli_fetch_assoc($result_sales);
                      echo ($sales['total_sold'] ?? 0) . " pcs";
                      ?>
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Deskripsi -->
            <div class="mb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">Deskripsi Produk</h3>
              <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex space-x-4">
              <button class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-3 py-2.5 text-center transition-all duration-200">
                <span class="material-symbols-rounded">
                  sms
                </span>
              </button>
              <button type="button" onclick="addToCart(<?php echo $product_id; ?>)"
                class="flex-1 text-blue-700 outline outline-2 outline-blue-700 hover:text-white hover:outline-none hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center transition-all duration-200">
                <span class="material-symbols-rounded text-sm mr-2">shopping_cart</span>
                Tambah ke Keranjang
              </button>
              <button type="button" onclick="buyNow(<?php echo $product_id; ?>)"
                class="flex-1 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-5 py-2.5 text-center transition-all duration-200">
                <span class="material-symbols-rounded text-sm mr-2">shopping_bag</span>
                Beli Sekarang
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- end of content -->
  <!--penilaian -->
  <section class="mb-2">
    <div class="max-w-screen-xl mx-auto px-4">
      <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
        <h2 class="text-2xl font-bold text-white mb-6">Penilaian Produk</h2>
        <div class="flex items-center mb-4">
          <div class="text-4xl font-bold text-red-600 mr-4">
            4.9 <span class="text-xl font-normal text-gray-700 dark:text-gray-300">dari 5</span>
          </div>
          <div class="flex text-yellow-400 text-2xl">★ ★ ★ ★ ★</div>
        </div>
        <!-- Filter/Tabs - Contoh Sederhana -->
        <div class="flex flex-wrap gap-2 mb-6">
          <span class="px-4 py-2 border rounded-md bg-red-600 text-white cursor-pointer">Semua</span>
          <span
            class="px-4 py-2 border rounded-md text-gray-700 dark:text-gray-300 dark:border-gray-600 cursor-pointer">5 Bintang (4,4RB)</span>
          <span
            class="px-4 py-2 border rounded-md text-gray-700 dark:text-gray-300 dark:border-gray-600 cursor-pointer">4 Bintang (508)</span>
          <span
            class="px-4 py-2 border rounded-md text-gray-700 dark:text-gray-300 dark:border-gray-600 cursor-pointer">3 Bintang (79)</span>
          <span
            class="px-4 py-2 border rounded-md text-gray-700 dark:text-gray-300 dark:border-gray-600 cursor-pointer">Dengan Komentar (1,1RB)</span>
          <span
            class="px-4 py-2 border rounded-md text-gray-700 dark:text-gray-300 dark:border-gray-600 cursor-pointer">Dengan Media (848)</span>
        </div>

        <!-- Contoh Ulasan -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
          <div class="flex items-center mb-2">
            <div
              class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-gray-700 dark:text-gray-300 font-semibold mr-3"></div>
            <div>
              <div class="font-semibold text-gray-900 dark:text-white">hastomo_14_tmj</div>
              <div class="flex text-yellow-400 text-sm">★ ★ ★ ★ ★</div>
            </div>
          </div>
          <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
            2022-03-12 18:54 | Variasi: Ukuran 43
          </p>
          <p class="text-gray-700 dark:text-gray-300 mb-4">
            Terimakasih seller sepatunya sudah sampai dengan selamat, sepatunya bagus dan keren
            sesuai dengan deskripsi, pengirimannya juga cepat. Packingnya juga rapi dan dapat
            masker juga top banget lah pokoknya. Rekomenlah pokoknya 👍 👍
          </p>
          <!-- Contoh Area Media (Gambar/Video) -->
          <div class="flex space-x-2 mb-4">
            <img
              src="img/product_1.png"
              alt="Review Media"
              class="w-16 h-16 object-cover rounded-md" />
            <img
              src="img/product_1.png"
              alt="Review Media"
              class="w-16 h-16 object-cover rounded-md" />
            <img
              src="img/product_1.png"
              alt="Review Media"
              class="w-16 h-16 object-cover rounded-md" />
          </div>
          <!-- Respon Penjual -->
          <div
            class="bg-gray-100 dark:bg-gray-700 rounded-md p-3 text-sm text-gray-700 dark:text-gray-300">
            <span class="font-semibold">Respon Penjual:</span> Terima kasih telah berbelanja di
            Tomkins Official. Follow Toko kami untuk terus update mengenai stok dan produk
            terbaru.
          </div>
          <div class="flex items-center mt-3 text-gray-500 dark:text-gray-400 text-sm">
            <svg
              class="w-4 h-4 mr-1"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg">
              <path
                fill-rule="evenodd"
                d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.339-4.638A13.956 13.956 0 0010 15c4.006 0 7.25-2.879 7.25-6.444 0-3.566-3.244-6.444-7.25-6.444S2.75 6.434 2.75 10a.5.5 0 011 0c0 2.76 2.794 5 6.25 5s6.25-2.24 6.25-5S13.506 3 10 3c-3.382 0-6.084 2.275-6.246 5.031a1 1 0 01-1.065.858A10.453 10.453 0 012 10c0-4.418 4.03-8 9-8s9 3.582 9 8z"
                clip-rule="evenodd"></path>
            </svg>
            328
          </div>
        </div>

        <!-- Anda bisa menambahkan ulasan lainnya di sini -->
      </div>
    </div>
  </section>
  <!-- end of penilaian -->

  <?php
  include 'inc_footer.php';
  ?>

  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
  <script>
    function addToCart(productId) {
      fetch('add_to_cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            alert(data.message);
          } else {
            if (data.message === 'Silakan login terlebih dahulu') {
              window.location.href = 'login.php';
            } else {
              alert(data.message);
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan saat menambahkan ke keranjang');
        });
    }

    function buyNow(productId) {
      fetch('add_to_cart.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `product_id=${productId}&quantity=1`
        })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            window.location.href = 'checkout.php';
          } else {
            if (data.message === 'Silakan login terlebih dahulu') {
              window.location.href = 'login.php';
            } else {
              alert(data.message);
            }
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Terjadi kesalahan saat memproses pembelian');
        });
    }
  </script>
</body>

</html>