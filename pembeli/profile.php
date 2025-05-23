<?php
include '../session.php';
include '../koneksi.php';

// Set user_id default ke 0 (guest) jika belum login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 0;
    $_SESSION['user_name'] = 'Guest';
}

$user_id = $_SESSION['user_id'];
$is_guest = ($user_id === 0);

// Ambil data user jika bukan guest
if (!$is_guest) {
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($is_guest) {
        $errors[] = "Silakan login terlebih dahulu untuk mengubah profil";
    } else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $errors = [];

        // Validasi email
        if ($email !== $user['email']) {
            $check_email = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' AND user_id != $user_id");
            if (mysqli_num_rows($check_email) > 0) {
                $errors[] = "Email sudah digunakan oleh pengguna lain";
            }
        }

        // Validasi password jika diisi
        if (!empty($current_password)) {
            if (!password_verify($current_password, $user['password'])) {
                $errors[] = "Password saat ini tidak sesuai";
            } elseif (empty($new_password)) {
                $errors[] = "Password baru harus diisi";
            } elseif ($new_password !== $confirm_password) {
                $errors[] = "Konfirmasi password baru tidak sesuai";
            }
        }

        if (empty($errors)) {
            $update_sql = "UPDATE users SET name = ?, email = ?";
            $params = [$name, $email];
            $types = "ss";

            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_sql .= ", password = ?";
                $params[] = $hashed_password;
                $types .= "s";
            }

            $update_sql .= " WHERE user_id = ?";
            $params[] = $user_id;
            $types .= "i";

            $stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($stmt, $types, ...$params);

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['user_name'] = $name;
                $success_message = "Profil berhasil diperbarui";
                // Refresh data user
                $result = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $user_id");
                $user = mysqli_fetch_assoc($result);
            } else {
                $errors[] = "Gagal memperbarui profil";
            }
        }
    }
}
?>

<?php include 'inc_header.php'; ?>

<body class="bg-gray-50" style="font-family: Inter;">
    <?php include 'inc_navbar.php'; ?>

    <div class="container mx-auto px-4 py-8 mt-16">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">Profil Saya</h1>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($is_guest): ?>
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-center mb-6">
                        <span class="material-symbols-rounded text-6xl text-gray-400">account_circle</span>
                        <h2 class="text-xl font-semibold text-gray-900 mt-4">Anda belum login</h2>
                        <p class="text-gray-600 mt-2">Silakan login atau daftar untuk mengakses fitur profil</p>
                    </div>
                    <div class="flex justify-center space-x-4">
                        <a href="../login.php" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Login
                        </a>
                        <a href="../register.php" class="bg-gray-600 text-white px-6 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
                            Daftar
                        </a>
                    </div>
                    <div class="mt-6 text-center">
                        <a href="#" class="text-blue-600 hover:text-blue-800">
                            Lupa password?
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <form method="POST" class="bg-white shadow rounded-lg p-6">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="border-t border-gray-200 my-6"></div>

                    <h2 class="text-lg font-medium text-gray-900 mb-4">Ubah Password</h2>
                    <div class="mb-4">
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                        <input type="password" id="current_password" name="current_password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                        <input type="password" id="new_password" name="new_password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-6">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="flex justify-between items-center">
                        <a href="forgot_password.php" class="text-blue-600 hover:text-blue-800">
                            Lupa password?
                        </a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../inc_footer.php'; ?>
</body>

</html>