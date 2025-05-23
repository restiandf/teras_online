<?php
include 'session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'inc_header.php';
include 'koneksi.php';

// Ambil data keranjang dari database
$user_id = $_SESSION['user_id'];
$sql = "SELECT c.cart_id, ci.item_id, ci.quantity, p.product_id, p.name, p.price, p.stock, pi.image_url 
        FROM cart c 
        JOIN cart_items ci ON c.cart_id = ci.cart_id 
        JOIN products p ON ci.product_id = p.product_id 
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = 1 
        WHERE c.user_id = ? AND c.status = 'active'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cart_items = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Hitung total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- end of navbar -->

    <div class="container mx-auto px-4 pt-24 pb-12">
        <h1 class="text-2xl font-bold text-gray-800 mb-8">Keranjang Belanja</h1>

        <?php if (empty($cart_items)): ?>
            <!-- Tampilan ketika keranjang kosong -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <div class="flex flex-col items-center justify-center py-8">
                    <span class="material-symbols-rounded text-gray-400 text-6xl mb-4">
                        shopping_cart
                    </span>
                    <h2 class="text-xl font-semibold text-gray-600 mb-2">Keranjang Belanja Kosong</h2>
                    <p class="text-gray-500 mb-6">Anda belum menambahkan produk ke keranjang belanja</p>
                    <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Mulai Belanja
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Tampilan ketika ada item di keranjang -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Item di Keranjang</h2>
                    <button onclick="clearCart()" class="text-red-600 hover:text-red-800 flex items-center gap-1">
                        <span class="material-symbols-rounded">delete</span>
                        Hapus Semua
                    </button>
                </div>

                <div class="space-y-4">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex items-center justify-between border-b pb-4" data-item-id="<?php echo $item['item_id']; ?>">
                            <div class="flex items-center space-x-4">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'img/no-image.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    class="w-20 h-20 object-cover rounded">
                                <div>
                                    <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-gray-500">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center border rounded">
                                    <button onclick="updateQuantity(<?php echo $item['item_id']; ?>, 'decrease')"
                                        class="px-3 py-1 hover:bg-gray-100">-</button>
                                    <span class="px-3 py-1 quantity"><?php echo $item['quantity']; ?></span>
                                    <button onclick="updateQuantity(<?php echo $item['item_id']; ?>, 'increase')"
                                        class="px-3 py-1 hover:bg-gray-100">+</button>
                                </div>
                                <button onclick="removeItem(<?php echo $item['item_id']; ?>)"
                                    class="text-red-600 hover:text-red-800">
                                    <span class="material-symbols-rounded">delete</span>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 flex justify-between items-center">
                    <div class="text-gray-600">
                        Total: <span class="font-semibold text-gray-800">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                    </div>
                    <button onclick="checkout()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                        Checkout
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- footer -->
    <?php include 'inc_footer.php'; ?>
    <!-- End Of Footer -->

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

    <script>
        function updateQuantity(itemId, action) {
            fetch('update_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `item_id=${itemId}&action=${action}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui keranjang');
                });
        }

        function removeItem(itemId) {
            if (confirm('Apakah Anda yakin ingin menghapus item ini?')) {
                fetch('remove_cart_item.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `item_id=${itemId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus item');
                    });
            }
        }

        function clearCart() {
            if (confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) {
                fetch('clear_cart.php', {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengosongkan keranjang');
                    });
            }
        }

        function checkout() {
            window.location.href = 'checkout.php';
        }
    </script>
</body>

</html>