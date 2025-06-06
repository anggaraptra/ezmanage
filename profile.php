<?php
require_once 'functions/functions.php';
cek_session();

$user_login = $_SESSION['user'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzManage - Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-blue-600 to-blue-400 text-white flex flex-col py-8 px-6 shadow-lg fixed inset-y-0 left-0 z-30">
            <div class="mb-10 flex items-center gap-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-2xl font-bold tracking-wide">EzManage</span>
            </div>
            <nav class="flex flex-col gap-4 mt-4">
                <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                    </svg>
                    Dashboard
                </a>
                <a href="todo.php" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Todo List
                </a>
                <a href="expenses.php" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                    </svg>
                    Pengeluaran
                </a>
                <a href="calculations.php" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v2M8 2v2M3 6h18M4 10h16M4 14h16M4 18h16" />
                        <rect x="8" y="14" width="8" height="6" rx="2" />
                    </svg>
                    Kalkulator
                </a>
            </nav>
            <div class="mt-auto pt-10 border-t border-white border-opacity-20">
                <a href="functions/logout.php" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                    </svg>
                    Logout
                </a>
            </div>
        </aside>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col ml-64">
            <!-- Navbar -->
            <header class="bg-white shadow flex items-center justify-between px-8 py-4 sticky top-0 z-20">
                <h1 class="text-2xl font-bold text-blue-600">Profile</h1>
                <div class="relative">
                    <button id="profileDropdownBtn" class="flex items-center gap-2 focus:outline-none">
                        <span class="font-semibold text-gray-700"><?= htmlspecialchars($user_login['fullname']) ?></span>
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($user_login['fullname']) ?>&background=4f8ef7&color=fff" alt="Profile" class="w-10 h-10 rounded-full border-2 border-blue-500 shadow">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded shadow-lg border z-30">
                        <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50">View Profile</a>
                    </div>
                </div>
            </header>
            <!-- Content -->
            <main class="flex-1 p-8">
                <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8">
                    <div class="flex flex-col items-center gap-4">
                        <!-- Profile Picture -->
                        <div class="relative group">
                            <img id="profileImage" src="https://ui-avatars.com/api/?name=<?= urlencode($user_login['fullname']) ?>&background=4f8ef7&color=fff" alt="Profile" class="w-28 h-28 rounded-full border-4 border-blue-500 shadow-lg object-cover">
                            <label for="profilePicInput" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 rounded-full cursor-pointer transition">
                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2H7a2 2 0 01-2-2v-2a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2z" />
                                </svg>
                                <input type="file" id="profilePicInput" class="hidden" accept="image/*">
                            </label>
                        </div>
                        <form class="w-full mt-6 space-y-6" method="post" enctype="multipart/form-data" action="profile_update.php">
                            <!-- Full Name -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2" for="fullname">Nama Lengkap</label>
                                <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user_login['fullname']) ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                            </div>
                            <!-- Email (readonly) -->
                            <div>
                                <label class="block text-gray-700 font-semibold mb-2" for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_login['email']) ?>" class="w-full px-4 py-2 border rounded-lg bg-gray-100 cursor-not-allowed" readonly>
                            </div>
                            <!-- Change Password Accordion -->
                            <div x-data="{ open: false }" class="border rounded-lg">
                                <button type="button" onclick="document.getElementById('passwordSection').classList.toggle('hidden')" class="w-full flex items-center justify-between px-4 py-3 text-blue-600 font-semibold focus:outline-none">
                                    Ubah Password
                                    <svg class="w-5 h-5 ml-2 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div id="passwordSection" class="hidden px-4 pb-4 pt-2 space-y-3">
                                    <div>
                                        <label class="block text-gray-700 mb-1" for="current_password">Password Lama</label>
                                        <input type="password" id="current_password" name="current_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-1" for="new_password">Password Baru</label>
                                        <input type="password" id="new_password" name="new_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    </div>
                                    <div>
                                        <label class="block text-gray-700 mb-1" for="confirm_password">Konfirmasi Password Baru</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    </div>
                                </div>
                            </div>
                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="assets/js/profile.js"></script>
</body>

</html>