<?php
require_once "functions/functions.php";

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzManage - Registrasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-100 to-blue-300 min-h-screen flex items-center justify-center">
    <main class="w-full max-w-lg mx-auto bg-white rounded-xl shadow-lg p-8">
        <header class="mb-6 text-center">
            <h1 class="text-3xl font-bold text-blue-700 mb-2">Registrasi Anggota</h1>
            <p class="text-gray-500">Silakan isi data diri Anda untuk mendaftar</p>
        </header>
        <form action="functions/sign_up.php" method="post" id="form-registrasi" class="space-y-5">
            <?php if ($msg = getFlash("register")): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?= $msg ?>
                </div>
            <?php endif; ?>
            <div>
                <label for="input-username" class="block text-gray-700 font-semibold mb-1">Username</label>
                <input type="text" name="username" id="input-username" required autofocus
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="input-password" class="block text-gray-700 font-semibold mb-1">Password</label>
                <input type="password" name="password" id="input-password" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="input-confirm" class="block text-gray-700 font-semibold mb-1">Konfirmasi Password</label>
                <input type="password" name="confirm" id="input-confirm" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="input-fullname" class="block text-gray-700 font-semibold mb-1">Nama Lengkap</label>
                <input type="text" name="fullname" id="input-fullname" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="input-email" class="block text-gray-700 font-semibold mb-1">Email</label>
                <input type="email" name="email" id="input-email" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div>
                <label for="input-city" class="block text-gray-700 font-semibold mb-1">Kota</label>
                <input type="text" name="city" id="input-city" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 w-full mr-2">
                    Daftar
                </button>
                <button type="reset"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded-lg transition duration-200 w-full ml-2">
                    Batal
                </button>
            </div>
            <div class="text-center mt-4">
                <span class="text-gray-600 text-sm">Sudah punya akun?</span>
                <a href="login_page.php" class="text-blue-600 hover:underline text-sm">Login di sini</a>
            </div>
        </form>
    </main>
</body>

</html>