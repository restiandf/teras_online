<?php
include 'session.php';

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'inc_header.php';
include 'koneksi.php';

// Konfigurasi RajaOngkir
$api_key = "568f1a71a258527138ddf23a2eba5945";
$base_url = "https://api.rajaongkir.com/starter";

// Fungsi untuk mengambil data provinsi
function getProvinces($api_key, $base_url)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "$base_url/province",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "key: $api_key"
        ),
    ));
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log("Curl Error: " . $err);
        return ['rajaongkir' => ['results' => []]];
    }

    if ($http_code !== 200) {
        error_log("API Error: HTTP Code " . $http_code);
        return ['rajaongkir' => ['results' => []]];
    }

    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Error: " . json_last_error_msg());
        return ['rajaongkir' => ['results' => []]];
    }

    return $data;
}

// Fungsi untuk mengambil data kota
function getCities($api_key, $base_url, $province_id)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "$base_url/city?province=$province_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "key: $api_key"
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

// Ambil data provinsi
$provinces = getProvinces($api_key, $base_url);

// Debug: Log response dari API
error_log("RajaOngkir Response: " . json_encode($provinces));

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

// Jika keranjang kosong, redirect ke halaman keranjang
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}
?>

<body class="bg-gray-50" style="font-family: 'Inter', sans-serif">
    <!-- navbar -->
    <?php include 'inc_navbar.php'; ?>
    <!-- end of navbar -->

    <div class="container mx-auto px-4 pt-24 pb-12">
        <h1 class="text-2xl font-bold text-gray-800 mb-8">Checkout</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Form Alamat Pengiriman -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Alamat Pengiriman</h2>
                <form id="checkoutForm" action="process_checkout.php" method="POST">
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Penerima</label>
                            <input type="text" id="name" name="name" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">Provinsi</label>
                            <select id="province" name="province" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Provinsi</option>
                                <?php
                                if (isset($provinces['rajaongkir']['results'])) {
                                    foreach ($provinces['rajaongkir']['results'] as $province) {
                                        echo '<option value="' . htmlspecialchars($province['province_id']) . '">' .
                                            htmlspecialchars($province['province']) . '</option>';
                                    }
                                } else {
                                    error_log("Province data structure: " . json_encode($provinces));
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700">Kota/Kabupaten</label>
                            <select id="city" name="city" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Kota/Kabupaten</option>
                            </select>
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Alamat Lengkap</label>
                            <textarea id="address" name="address" rows="3" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        </div>
                        <div>
                            <label for="courier" class="block text-sm font-medium text-gray-700">Kurir</label>
                            <select id="courier" name="courier" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Kurir</option>
                                <option value="jne">JNE</option>
                                <option value="pos">POS Indonesia</option>
                                <option value="tiki">TIKI</option>
                            </select>
                        </div>
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                            <select id="payment_method" name="payment_method" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih metode pembayaran</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="ewallet">E-Wallet</option>
                                <option value="cod">Cash on Delivery</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Ringkasan Pesanan -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h2>
                <div class="space-y-4">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="flex items-center justify-between border-b pb-4">
                            <div class="flex items-center space-x-4">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?? 'img/no-image.jpg'); ?>"
                                    alt="<?php echo htmlspecialchars($item['name']); ?>"
                                    class="w-16 h-16 object-cover rounded">
                                <div>
                                    <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($item['name']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                            <p class="font-medium text-gray-800">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                        </div>
                    <?php endforeach; ?>

                    <div class="border-t pt-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium text-gray-800">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-600">Pengiriman</span>
                            <span id="shipping_cost" class="font-medium text-gray-800">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center text-lg font-bold mt-4">
                            <span>Total</span>
                            <span id="total_amount">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                    </div>

                    <button type="submit" form="checkoutForm"
                        class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        Bayar Sekarang
                    </button>

                </div>
                <div class="mt-4 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">*Tunggu sampai biaya pengiriman muncul sebelum melanjutkan pembayaran</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- footer -->
    <?php include 'inc_footer.php'; ?>
    <!-- End Of Footer -->

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        // Fungsi untuk menampilkan pesan error
        function showError(message) {
            alert('Error: ' + message);
            console.error(message);
        }

        // Debug: Log data provinsi saat halaman dimuat
        console.log('Province data:', <?php echo json_encode($provinces); ?>);

        // Fungsi untuk mengambil data kota berdasarkan provinsi
        document.getElementById('province').addEventListener('change', function() {
            const provinceId = this.value;
            const citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';

            console.log('Selected province ID:', provinceId);

            if (provinceId) {
                fetch(`get_cities.php?province_id=${provinceId}`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('API Response:', data);

                        if (data.error) {
                            showError(data.error);
                            return;
                        }

                        if (!data.rajaongkir || !data.rajaongkir.results) {
                            showError('Format data tidak valid');
                            return;
                        }

                        data.rajaongkir.results.forEach(city => {
                            const option = document.createElement('option');
                            option.value = city.city_id;
                            option.textContent = city.type + ' ' + city.city_name;
                            citySelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showError('Gagal mengambil data kota: ' + error.message);
                    });
            }
        });

        // Fungsi untuk menghitung biaya pengiriman
        document.getElementById('courier').addEventListener('change', function() {
            const cityId = document.getElementById('city').value;
            const courier = this.value;

            console.log('Selected city ID:', cityId);
            console.log('Selected courier:', courier);

            if (!cityId) {
                showError('Silakan pilih kota terlebih dahulu');
                this.value = '';
                return;
            }

            if (cityId && courier) {
                fetch(`get_shipping_cost.php?city_id=${cityId}&courier=${courier}`)
                    .then(response => {
                        console.log('Response status:', response.status);
                        return response.json();
                    })
                    .then(data => {
                        console.log('API Response:', data);

                        if (data.error) {
                            showError(data.error);
                            return;
                        }

                        if (!data.rajaongkir || !data.rajaongkir.results || !data.rajaongkir.results[0].costs) {
                            showError('Format data biaya pengiriman tidak valid');
                            return;
                        }

                        const shippingCost = data.rajaongkir.results[0].costs[0].cost[0].value;
                        document.getElementById('shipping_cost').textContent = `Rp ${shippingCost.toLocaleString('id-ID')}`;

                        const subtotal = <?php echo $total; ?>;
                        const total = subtotal + shippingCost;
                        document.getElementById('total_amount').textContent = `Rp ${total.toLocaleString('id-ID')}`;
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                        showError('Gagal menghitung biaya pengiriman: ' + error.message);
                    });
            }
        });
    </script>
</body>

</html>