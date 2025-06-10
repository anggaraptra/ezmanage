<?php
require_once 'functions/functions.php';

// Check cookie
if (isset($_COOKIE['id']) && isset($_COOKIE['key'])) {
    $id = $_COOKIE['id'];
    $key = $_COOKIE['key'];

    // Get username by id
    $result = dbquery("SELECT username, fullname, email FROM users WHERE id = $id");
    $row = mysqli_fetch_assoc($result);

    // Check cookie and username
    if ($key === hash('sha256', $row['username'])) {
        $_SESSION['login'] = true;
        $_SESSION['user']['id'] = $id;
    }
}

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
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='blue' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E">
    <title>EzManage - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>

<body class="bg-gradient-to-br from-blue-100 to-blue-300 min-h-screen flex items-center justify-center font-sans dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800 transition-colors duration-300">
    <main class="w-full max-w-md mx-auto bg-white rounded-xl shadow-lg p-8 dark:bg-gray-900 dark:shadow-blue-900/40 transition-colors duration-300">
        <header class="mb-6 text-center">
            <div class="flex items-center justify-center gap-3 mb-2">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-2xl font-bold tracking-wide text-blue-700 dark:text-blue-200">EzManage</span>
            </div>
            <h1 class="text-3xl font-bold text-blue-700 mb-2 dark:text-blue-200">Masuk ke EzManage</h1>
            <p class="text-gray-500 dark:text-gray-400">Kelola aktivitas produktif Anda dengan mudah</p>
        </header>
        <form action="functions/login.php" method="post" id="form-login" class="space-y-5">
            <?php if ($msg = getFlash("auth")): ?>
                <?= $msg ?>
            <?php endif; ?>
            <div>
                <label for="input-username" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Username</label>
                <input type="text" name="username" id="input-username" required autofocus
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 transition-colors duration-300">
            </div>
            <div>
                <label for="input-password" class="block text-gray-700 font-semibold mb-1 dark:text-gray-200">Password</label>
                <input type="password" name="password" id="input-password" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 transition-colors duration-300">
            </div>
            <div class="flex items-center justify-between">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remember" class="form-checkbox text-blue-600 dark:bg-gray-800 dark:border-gray-700">
                    <span class="ml-2 text-gray-700 dark:text-gray-200">Remember me</span>
                </label>
                <a href="forgot_password.php" class="text-blue-600 hover:underline text-sm dark:text-blue-400">Lupa password?</a>
            </div>
            <div>
                <button type="submit" name="login" id="btn-login"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 dark:bg-blue-700 dark:hover:bg-blue-800">
                    Login
                </button>
            </div>
            <div class="text-center text-gray-600 mt-4 dark:text-gray-300">
                Belum punya akun? <a href="register.php" class="text-blue-600 hover:underline dark:text-blue-400">Daftar sekarang</a>
            </div>
        </form>
    </main>
    <script>
        // Dark mode toggle logic
        const html = document.documentElement;
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    </script>
</body>

</html>