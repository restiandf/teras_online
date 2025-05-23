<?php
include 'inc_header.php';
include 'koneksi.php';

session_start();

$success_message = '';
$error_message = '';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO messages (user_id, name, email, subject, message) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $user_id, $name, $email, $subject, $message);

    if (mysqli_stmt_execute($stmt)) {
        $success_message = "Pesan Anda telah berhasil dikirim. Kami akan segera menghubungi Anda.";
    } else {
        $error_message = "Maaf, terjadi kesalahan. Silakan coba lagi nanti.";
    }
}
?>

<body>
    <?php include 'inc_navbar.php'; ?>

    <section class="pt-24 pb-12">
        <div class="max-w-screen-xl mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Form Kontak -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Hubungi Kami</h2>

                    <?php if ($success_message): ?>
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subjek</label>
                            <input type="text" id="subject" name="subject" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Pesan</label>
                            <textarea id="message" name="message" rows="4" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            Kirim Pesan
                        </button>
                    </form>
                </div>

                <!-- Informasi Kontak -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Informasi Kontak</h2>

                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <span class="material-symbols-rounded text-blue-600">location_on</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Alamat</h3>
                                <p class="text-gray-600">Jl. Kemenangan, Jakarta</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <span class="material-symbols-rounded text-blue-600">phone</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Telepon</h3>
                                <p class="text-gray-600">+62 123 4567 890</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <span class="material-symbols-rounded text-blue-600">mail</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Email</h3>
                                <p class="text-gray-600">info@terasonline.com</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <span class="material-symbols-rounded text-blue-600">schedule</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">Jam Operasional</h3>
                                <p class="text-gray-600">Senin - Minggu: 08:00 - 21:00 WIB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Peta Lokasi -->
                    <div class="mt-8">
                        <h3 class="font-semibold text-gray-900 mb-4">Lokasi Kami</h3>
                        <div class="aspect-w-16 aspect-h-9">
                            <iframe
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d126748.6091242777!2d106.702711!3d-6.2295715!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e945e34b9d%3A0x5371bf0fdad786a2!2sJakarta!5e0!3m2!1sen!2sid!4v1234567890"
                                class="w-full h-64 rounded-lg"
                                style="border:0;"
                                allowfullscreen=""
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'inc_footer.php'; ?>
</body>

</html>