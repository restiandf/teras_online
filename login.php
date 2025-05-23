<?php
include 'session.php';

// Cek jika pengguna sudah login, redirect ke index.php
if (isset($_SESSION['user_id'])) {
  header("Location: index.php");
  exit();
}

include 'inc_header.php';
?>

<body class="bg-white" style="font-family: 'Inter', sans-serif">
  <!-- navbar -->
  <?php
  include 'inc_navbar.php';
  ?>
  <!-- end of navbar -->
  <section class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
    <div class="pt-20 text-blue-800 font-bold mb-2">
      <h1 class="text-3xl">Masuk</h1>
    </div>

    <hr class="w-full border-t border-gray-200 mb-5">

    <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
      <div class="w-full border border-gray-200 rounded-lg p-4">
        <!-- Pesan Status -->
        <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
          <div id="success-message" class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
            Pendaftaran berhasil! Silakan masuk.
          </div>
        <?php elseif (isset($_GET['error'])): ?>
          <div id="error-message" class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
            <?php
            $error = $_GET['error'];
            if ($error == 'emptyfields') {
              echo "Mohon isi semua kolom.";
            } elseif ($error == 'invalidcredentials') {
              echo "Email atau kata sandi salah.";
            } elseif ($error == 'dberror') {
              echo "Terjadi kesalahan pada database. Silakan coba lagi nanti.";
            } else {
              echo "Terjadi kesalahan.";
            }
            ?>
          </div>
        <?php endif; ?>
        <!-- End Pesan Status -->
        <form class="w-full" action="proses_login.php" method="POST">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="mb-5">
              <label for="email" class="block mb-2 text-sm font-medium text-gray-900">Email</label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                  <span class="material-symbols-rounded text-gray-500 dark:text-gray-400 w-5 h-5">mail</span>
                </div>
                <input
                  type="email"
                  id="email"
                  name="email"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-3"
                  placeholder="name@mail.com"
                  required />
              </div>
            </div>
            <div class="mb-5">
              <label for="password" class="block mb-2 text-sm font-medium text-gray-900">Kata Sandi</label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                  <span class="material-symbols-rounded text-gray-500 dark:text-gray-400 w-5 h-5">lock</span>
                </div>
                <input
                  type="password"
                  id="password"
                  name="password"
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-3"
                  placeholder="••••••••"
                  required="" />
              </div>
            </div>
          </div>
          <button
            type="submit"
            class="w-full p-3 text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
            Masuk
          </button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-600">
          Belum punya akun?
          <a href="register.php" class="font-medium text-blue-700 hover:text-blue-800 hover:underline transition-colors duration-300">
            Daftar di sini
          </a>
        </p>
        <p class="mt-4 text-sm text-yellow-600 italic flex items-center gap-2">
          <span class="material-symbols-rounded text-yellow-500">info</span>
          Jika lupa password silahkan hubungi kontak kami melalui email atau nomor telepon
        </p>
      </div>


      <div>
        <img src="img/bg.jpg" alt="" class="rounded-md" />
      </div>
    </div>
  </section>
  <!-- footer -->

  <?php
  include 'inc_footer.php';
  ?>

  <!-- End Of Footer -->
  <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

  <!-- Script untuk menghilangkan pesan setelah 3 detik -->
  <script>
    // Fungsi untuk menghilangkan pesan
    function hideMessage(elementId) {
      const element = document.getElementById(elementId);
      if (element) {
        setTimeout(() => {
          element.style.display = 'none';
        }, 3000); // 3000 ms = 3 detik
      }
    }

    // Cek dan hilangkan pesan sukses
    hideMessage('success-message');
    // Cek dan hilangkan pesan error
    hideMessage('error-message');
  </script>
</body>

</html>