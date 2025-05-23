<?php
include '../session.php';

// Cek jika pengguna belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
  header("Location: ../login.php");
  exit();
}

include '../koneksi.php';

// Ambil total produk
$sql_products = "SELECT COUNT(*) as total FROM products";
$result_products = mysqli_query($conn, $sql_products);
$total_products = mysqli_fetch_assoc($result_products)['total'];

// Ambil total pesanan
$sql_orders = "SELECT COUNT(*) as total FROM orders";
$result_orders = mysqli_query($conn, $sql_orders);
$total_orders = mysqli_fetch_assoc($result_orders)['total'];

// Ambil total pengguna
$sql_users = "SELECT COUNT(*) as total FROM users WHERE role = 'pembeli'";
$result_users = mysqli_query($conn, $sql_users);
$total_users = mysqli_fetch_assoc($result_users)['total'];

// Ambil total pendapatan
$sql_revenue = "SELECT COALESCE(SUM(grand_total), 0) as total FROM orders WHERE status != 'cancelled'";
$result_revenue = mysqli_query($conn, $sql_revenue);
$total_revenue = mysqli_fetch_assoc($result_revenue)['total'];

// Ambil total pesanan berdasarkan status
$sql_orders_status = "SELECT status, COUNT(*) as total FROM orders GROUP BY status";
$result_orders_status = mysqli_query($conn, $sql_orders_status);
$orders_by_status = [];
while ($row = mysqli_fetch_assoc($result_orders_status)) {
  $orders_by_status[$row['status']] = $row['total'];
}

// Ambil pendapatan bulan ini
$sql_monthly_revenue = "SELECT COALESCE(SUM(grand_total), 0) as total FROM orders WHERE status != 'cancelled' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
$result_monthly_revenue = mysqli_query($conn, $sql_monthly_revenue);
$monthly_revenue = mysqli_fetch_assoc($result_monthly_revenue)['total'];

// Ambil produk terlaris
$sql_best_seller = "SELECT p.name, SUM(oi.quantity) as total_sold 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.product_id 
                    GROUP BY p.product_id 
                    ORDER BY total_sold DESC 
                    LIMIT 5";
$result_best_seller = mysqli_query($conn, $sql_best_seller);
$best_sellers = mysqli_fetch_all($result_best_seller, MYSQLI_ASSOC);

include 'inc_header.php';
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
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
    <div class=" rounded-lg mt-14">
      <!-- Welcome Section -->
      <div class="grid grid-cols-1 gap-4 mb-4">
        <div class="flex flex-col justify-start p-6 h-24 rounded bg-blue-600 border border-blue-700">
          <p class="text-2xl font-extrabold text-white relative z-10">Selamat Datang, <?php echo $_SESSION['user_name']; ?> <span class="material-symbols-rounded text-3xl align-middle">waving_hand</span></p>
          <p class="text-sm text-white italic mt-1">"Selamat beraktifitas, semoga hari ini menyenangkan!"</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-600 text-sm font-medium">Total Produk</p>
              <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo number_format($total_products); ?></p>
            </div>
            <span class="material-symbols-rounded text-4xl text-blue-600">shopping_bag</span>
          </div>
        </div>

        <div class="bg-emerald-50 rounded-lg p-6 border border-emerald-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-emerald-600 text-sm font-medium">Total Pesanan</p>
              <p class="text-2xl font-bold text-emerald-600 mt-1"><?php echo number_format($total_orders); ?></p>
            </div>
            <span class="material-symbols-rounded text-4xl text-emerald-600">receipt_long</span>
          </div>
        </div>

        <div class="bg-violet-50 rounded-lg p-6 border border-violet-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-violet-600 text-sm font-medium">Total Pengguna</p>
              <p class="text-2xl font-bold text-violet-600 mt-1"><?php echo number_format($total_users); ?></p>
            </div>
            <span class="material-symbols-rounded text-4xl text-violet-600">group</span>
          </div>
        </div>

        <div class="bg-amber-50 rounded-lg p-6 border border-amber-200">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-amber-600 text-sm font-medium">Total Pendapatan</p>
              <p class="text-2xl font-bold text-amber-600 mt-1">Rp <?php echo number_format($total_revenue, 0, ',', '.'); ?></p>
            </div>
            <span class="material-symbols-rounded text-4xl text-amber-600">payments</span>
          </div>
        </div>
      </div>

      <!-- Additional Stats -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4 ">
        <!-- Order Status -->
        <div class="bg-white rounded-lg p-6 hover:shadow-xl transition-shadow duration-300 border border-gray-200">
          <h3 class="text-lg font-semibold text-blue-500 mb-4 flex items-center">
            <span class="material-symbols-rounded text-blue-500 mr-2">inventory_2</span>
            Status Pesanan
          </h3>
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300 border border-blue-200">
              <p class="text-sm text-blue-600 font-medium">Menunggu Pembayaran</p>
              <p class="text-2xl font-bold text-blue-700 mt-1"><?php echo number_format($orders_by_status['pending'] ?? 0); ?></p>
            </div>
            <div class="bg-yellow-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300 border border-yellow-200">
              <p class="text-sm text-yellow-600 font-medium">Diproses</p>
              <p class="text-2xl font-bold text-yellow-700 mt-1"><?php echo number_format($orders_by_status['processing'] ?? 0); ?></p>
            </div>
            <div class="bg-green-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300  border border-green-200">
              <p class="text-sm text-green-600 font-medium">Dikirim</p>
              <p class="text-2xl font-bold text-green-700 mt-1"><?php echo number_format($orders_by_status['shipped'] ?? 0); ?></p>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 hover:shadow-md transition-shadow duration-300 border border-purple-200">
              <p class="text-sm text-purple-600 font-medium">Selesai</p>
              <p class="text-2xl font-bold text-purple-700 mt-1"><?php echo number_format($orders_by_status['completed'] ?? 0); ?></p>
            </div>
          </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-lg p-6 hover:shadow-xl transition-shadow duration-300 border border-gray-200">
          <h3 class="text-lg font-semibold text-green-500 mb-4 flex items-center">
            <span class="material-symbols-rounded text-green-500 mr-2">trending_up</span>
            Pendapatan Bulan Ini
          </h3>
          <div class="bg-green-50 border border-green-200 rounded-lg p-6 relative overflow-hidden">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAzNGMwIDIuMjA5IDEuNzkxIDQgNCA0czQtMS43OTEgNC00LTQtMS43OTEtNC00LTQtNC00IDEuNzkxLTQgNHoiIGZpbGw9IiNmZmYiIGZpbGwtb3BhY2l0eT0iLjEiLz48L2c+PC9zdmc+')] opacity-10"></div>
            <div class="relative z-10">
              <p class="text-3xl font-bold text-green-600">Rp <?php echo number_format($monthly_revenue, 0, ',', '.'); ?></p>
              <p class="text-green-600 mt-2">Pendapatan <?php echo date('F Y'); ?></p>
            </div>
          </div>
        </div>
      </div>

      <!-- Best Selling Products -->
      <div class="bg-white rounded-lg p-6 hover:shadow-xl transition-shadow duration-300 border border-gray-200">
        <h3 class="text-lg font-semibold text-amber-500 mb-4 flex items-center">
          <span class="material-symbols-rounded text-amber-500 mr-2">star</span>
          Produk Terlaris
        </h3>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-amber-50">
                <th class="px-4 py-3 text-left text-sm font-medium text-amber-500">Produk</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-amber-500">Total Terjual</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($best_sellers as $product): ?>
                <tr class="hover:bg-gray-50 transition-colors duration-200">
                  <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($product['name']); ?></td>
                  <td class="px-4 py-3 text-sm text-gray-900 text-right"><?php echo number_format($product['total_sold']); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
</body>

</html>