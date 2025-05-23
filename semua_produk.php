<?php
include 'inc_header.php';
include 'koneksi.php';

// Ambil produk populer (contoh: produk dengan stok > 0)
$sql_popular = "SELECT p.*, pi.image_url 
                FROM products p 
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
                WHERE p.stock > 0 
                ORDER BY p.product_id DESC 
                LIMIT 4";
$result_popular = mysqli_query($conn, $sql_popular);

// Ambil semua produk dengan filter pencarian
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql_products = "SELECT p.*, pi.image_url, 
                COALESCE(SUM(oi.quantity), 0) as total_sold
                FROM products p 
                LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
                LEFT JOIN order_items oi ON p.product_id = oi.product_id
                WHERE p.name LIKE ? OR p.description LIKE ?
                GROUP BY p.product_id, p.name, p.description, p.price, p.stock, p.created_at, p.updated_at, pi.image_url
                ORDER BY p.product_id DESC";
$stmt = mysqli_prepare($conn, $sql_products);
$search_param = "%$search%";
mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
mysqli_stmt_execute($stmt);
$result_products = mysqli_stmt_get_result($stmt);
$total_products = mysqli_num_rows($result_products);
?>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teras Online</title>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .font-pacifico {
      font-family: 'Pacifico', cursive;
    }
  </style>
</head>

<body>
  <!-- Navbar -->

  <?php
  include 'inc_navbar.php';
  ?>
  <!-- End Of Navbar -->

  <!-- Jumbotron -->
  <section class="bg-white lg:py-16 dark:bg-gray-900 relative">
    <div class="absolute inset-0">
      <img src="img/banner.jpg" alt="Banner" class="w-full h-full object-cover opacity-20">
    </div>
    <div class="px-4 mx-auto max-w-screen-xl text-center z-10 relative pt-24 pb-8">
      <h1 class="mb-4 text-4xl font-extrabold tracking-tight leading-none text-gray-900 md:text-5xl lg:text-6xl dark:text-white">
        Teras Online
      </h1>
      <p class="mb-8 text-lg font-normal text-gray-500 lg:text-xl sm:px-16 lg:px-48 dark:text-gray-200">
        Temukan berbagai produk berkualitas dengan harga terbaik
      </p>
    </div>
  </section>
  <!-- End Of Jumbotron -->
  <!-- content -->

  <section class="py-8">
    <div class="max-w-screen-xl mx-auto px-4">
      <div class="flex flex-col md:flex-row justify-between items-center mb-6 bg-blue-50 border border-blue-200 py-6 px-8 rounded-2xl shadow-md">
        <div class="mb-4 md:mb-0">
          <h2 class="text-3xl font-pacifico text-blue-900 flex items-center gap-2">
            <span class="material-symbols-rounded text-blue-900">inventory_2</span>
            Semua Produk
          </h2>
        </div>
        <div class="w-full md:w-1/3">
          <form action="" method="GET" class="w-full mx-auto">
            <label
              for="search"
              class="mb-2 text-sm font-medium text-gray-900 sr-only">Cari Produk</label>
            <div class="relative group">
              <div
                class="absolute inset-y-0 rtl:inset-x-0 start-0 flex items-center ps-3.5 pointer-events-none">
                <svg
                  class="w-4 h-4 text-blue-500 group-hover:text-blue-600 transition-colors duration-300"
                  aria-hidden="true"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 20 20">
                  <path
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                </svg>
              </div>
              <input
                type="search"
                id="search"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                class="block w-full p-4 ps-10 text-sm text-gray-900 border border-blue-200 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 shadow-sm hover:shadow-md"
                placeholder="Cari produk..."
                required />
              <div class="absolute inset-y-0 end-0 flex items-center pe-2 gap-1">
                <?php if (!empty($search)): ?>
                  <a href="semua_produk.php" class="text-gray-500 hover:text-blue-600 px-2 transition-colors duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </a>
                <?php endif; ?>
                <button
                  type="submit"
                  class="text-white bg-blue-600 hover:bg-blue-700 focus:ring-2 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-4 py-2 transition-all duration-300 shadow-sm hover:shadow-md flex items-center gap-1">
                  <span class="material-symbols-rounded text-sm">search</span>
                  Cari
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if ($total_products > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result_products)): ?>
            <a href="<?php echo $row['stock'] > 0 ? 'product-detail.php?id=' . $row['product_id'] : '#'; ?>" class="<?php echo $row['stock'] <= 0 ? 'cursor-not-allowed' : ''; ?>">
              <div class="<?php echo $row['stock'] <= 0 ? 'bg-gray-200' : 'bg-blue-50 border border-blue-200 hover:shadow-lg'; ?> rounded-lg overflow-hidden relative">
                <?php if ($row['stock'] <= 0): ?>
                  <div class="absolute top-2 right-2 bg-gray-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                    Habis
                  </div>
                <?php endif; ?>
                <img src="<?php echo $row['image_url']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="h-48 mx-auto object-cover w-full <?php echo $row['stock'] <= 0 ? 'opacity-50' : ''; ?>" />
                <div class="p-4">
                  <h3 class="text-lg font-semibold <?php echo $row['stock'] <= 0 ? 'text-gray-600' : 'text-blue-600'; ?>">
                    <?php echo htmlspecialchars($row['name']); ?>
                  </h3>
                  <div class="flex items-center justify-between mt-2 <?php echo $row['stock'] <= 0 ? 'bg-gray-100' : 'bg-blue-100'; ?> p-2 rounded-lg">
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4 <?php echo $row['stock'] <= 0 ? 'text-gray-600' : 'text-blue-600'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                      </svg>
                      <span class="text-sm <?php echo $row['stock'] <= 0 ? 'text-gray-600' : 'text-blue-600'; ?>">Stok: <span class="font-semibold"><?php echo $row['stock']; ?></span></span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                      </svg>
                      <span class="text-sm <?php echo $row['stock'] <= 0 ? 'text-gray-600' : 'text-blue-600'; ?>">
                        Terjual: <span class="font-semibold"><?php echo $row['total_sold']; ?></span>
                      </span>
                    </div>
                  </div>
                  <button class="mt-4 w-full <?php echo $row['stock'] <= 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'; ?> text-white py-2 rounded-md">
                    <?php echo $row['stock'] <= 0 ? 'Stok Habis' : 'Beli Sekarang'; ?>
                  </button>
                </div>
              </div>
            </a>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="col-span-full text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Produk tidak ditemukan</h3>
            <p class="mt-1 text-sm text-gray-500">
              <?php if (!empty($search)): ?>
                Tidak ada produk yang sesuai dengan pencarian "<?php echo htmlspecialchars($search); ?>"
              <?php else: ?>
                Belum ada produk yang tersedia
              <?php endif; ?>
            </p>
            <?php if (!empty($search)): ?>
              <div class="mt-6">
                <a href="semua_produk.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                  Lihat Semua Produk
                </a>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <!-- footer -->
  <?php
  include 'inc_footer.php';
  ?>

  <!-- End Of Footer -->
</body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

</html>