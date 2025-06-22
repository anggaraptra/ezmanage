<?php
// Memanggil file functions.php yang berisi fungsi-fungsi yang dibutuhkan
require_once "functions/functions.php";

// Mengecek apakah user sudah login
if (isset($_SESSION['login'])) {
    // Jika sudah login, redirect ke halaman dashboard
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon SVG -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='blue' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E">
    <title>EzManage - Registrasi</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi Tailwind untuk dark mode
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>

<body class="bg-gradient-to-br from-blue-100 to-blue-300 min-h-screen flex items-center justify-center font-sans dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800">
    <!-- Main Container -->
    <main class="w-full max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 dark:bg-gray-900 dark:shadow-blue-900/40">
        <!-- Header Registrasi  -->
        <header class="mb-6 text-center">
            <div class="flex items-center justify-center gap-3 mb-2">
                <!-- Logo EzManage -->
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-2xl font-bold tracking-wide text-blue-700 dark:text-blue-200">EzManage</span>
            </div>
            <!-- Judul Halaman Registrasi -->
            <h1 class="text-3xl font-bold text-blue-700 mb-2 dark:text-blue-200">Daftar Akun EzManage</h1>
            <!-- Deskripsi Singkat -->
            <p class="text-gray-500 dark:text-gray-400">Buat akun baru untuk mulai mengelola aktivitas Anda</p>
        </header>
        <!-- Form Registrasi -->
        <form action="functions/sign_up.php" method="post" id="form-registrasi" class="space-y-5">
            <!-- Menampilkan pesan flash jika ada -->
            <?php if ($flash = getFlash("auth")): ?>
                <div id="flash">
                    <?= $flash ?>
                </div>
            <?php endif; ?>
            <!-- Input Data User -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Username & Password -->
                <div class="space-y-4">
                    <!-- Input Username -->
                    <div>
                        <label for="input-username" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Username</label>
                        <input type="text" name="username" id="input-username" required autofocus
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                        <div id="username-feedback" class="text-sm mt-1 dark:text-gray-400"></div>
                    </div>
                    <!-- Input Password -->
                    <div>
                        <label for="input-password" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Password</label>
                        <input type="password" name="password" id="input-password" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                    </div>
                    <!-- Input Konfirmasi Password -->
                    <div>
                        <label for="input-confirm" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Konfirmasi Password</label>
                        <input type="password" name="confirm" id="input-confirm" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                    </div>
                </div>
                <!-- Kolom Kanan: Data Pribadi -->
                <div class="space-y-4">
                    <!-- Input Nama Lengkap -->
                    <div>
                        <label for="input-fullname" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Nama Lengkap</label>
                        <input type="text" name="fullname" id="input-fullname" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                    </div>
                    <!-- Input Email -->
                    <div>
                        <label for="input-email" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Email</label>
                        <input type="email" name="email" id="input-email" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                    </div>
                    <!-- Input Kota -->
                    <div>
                        <label for="input-city" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Kota</label>
                        <input type="text" name="city" id="input-city" required
                            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                    </div>
                </div>
            </div>
            <!-- Tombol Aksi -->
            <div class="flex flex-col md:flex-row items-center justify-center gap-4 mt-4">
                <!-- Tombol Daftar -->
                <button type="submit"
                    class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition duration-200 dark:bg-blue-700 dark:hover:bg-blue-800">
                    Daftar
                </button>
                <!-- Tombol Reset -->
                <button type="reset"
                    class="w-full md:w-auto bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg transition duration-200 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200">
                    Batal
                </button>
            </div>
            <!-- Link ke Halaman Login -->
            <div class="text-center text-gray-600 mt-4 dark:text-gray-300">
                Sudah punya akun? <a href="login_page.php" class="text-blue-600 hover:underline dark:text-blue-400">Masuk di sini</a>
            </div>
        </form>
    </main>
    <script>
        // Logika toggle dark mode berdasarkan localStorage atau preferensi sistem
        const html = document.documentElement;
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        // Flash message auto-hide
        setTimeout(function() {
            let flash = document.getElementById('flash');
            if (flash) flash.style.display = 'none';
        }, 3500);
    </script>
</body>

</html>