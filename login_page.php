<?php
require_once 'functions/functions.php';
if (isset($_SESSION['user'])) {
    // Jika sudah login, redirect ke halaman dashboard
    header('Location: dashboard.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzManage - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-100 to-blue-300 min-h-screen flex items-center justify-center">
    <main class="w-full max-w-md mx-auto bg-white rounded-xl shadow-lg p-8">
        <header class="mb-6 text-center">
            <h1 class="text-3xl font-bold text-blue-700 mb-2">Login Anggota</h1>
            <p class="text-gray-500">Silakan masuk untuk melanjutkan</p>
        </header>
        <form action="functions/login.php" method="post" id="form-login" class="space-y-5">
            <?php if ($msg = getFlash("auth")): ?>
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
            <div class="flex items-center justify-between">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remember" class="form-checkbox text-blue-600">
                    <span class="ml-2 text-gray-700">Remember me</span>
                </label>
                <a href="forgot_password.php" class="text-blue-600 hover:underline text-sm">Lupa password?</a>
            </div>
            <div>
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200">
                    Login
                </button>
            </div>
            <div class="text-center text-gray-600 mt-4">
                Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline">Daftar sekarang</a>
            </div>
        </form>
    </main>
</body>

</html>