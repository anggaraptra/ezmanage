<?php
require_once 'functions/functions.php';
cek_session();

// Mengambil data user yang sedang login dari session
$user_login = $_SESSION['user'];

// Ambil data user 
$user = get_user_by_id($user_login['id']);
// Cek apakah user sudah upload foto profile, jika belum gunakan avatar default
$profilePic = !empty($user['profile_pic']) && file_exists('assets/profiles/' . $user['profile_pic'])
    ? 'assets/profiles/' . $user['profile_pic']
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']) . '&background=4f8ef7&color=fff';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon SVG -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='blue' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E">
    <title>EzManage - Profile</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi Tailwind untuk dark mode
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>

<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen font-sans dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar Navigasi -->
        <aside class="w-20 md:w-60 bg-white border-r border-blue-100 flex flex-col py-6 px-2 md:px-6 shadow-lg fixed inset-y-0 left-0 z-30 dark:bg-gray-900 dark:border-gray-800">
            <!-- Logo dan Judul Sidebar -->
            <div class="mb-10 flex items-center justify-center md:justify-start gap-3">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="hidden md:inline text-2xl font-bold tracking-wide text-blue-700 dark:text-blue-200">EzManage</span>
            </div>
            <!-- Menu Navigasi Sidebar -->
            <nav class="flex flex-col gap-2 mt-4">
                <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                    </svg>
                    <span class="hidden md:inline">Dashboard</span>
                </a>
                <a href="todo.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="hidden md:inline">Todo List</span>
                </a>
                <a href="expenses.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                    </svg>
                    <span class="hidden md:inline">Pengeluaran</span>
                </a>
                <a href="calculations.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v2M8 2v2M3 6h18M4 10h16M4 14h16M4 18h16" />
                        <rect x="8" y="14" width="8" height="6" rx="2" />
                    </svg>
                    <span class="hidden md:inline">Kalkulator</span>
                </a>
            </nav>
        </aside>
        <!-- Main Content Utama -->
        <div class="flex-1 flex flex-col md:ml-60 ml-20">
            <!-- Navbar Atas -->
            <header class="bg-white/80 backdrop-blur shadow-sm flex items-center justify-between px-4 md:px-10 py-4 sticky top-0 z-20 dark:bg-gray-900/80 dark:shadow-gray-900/30">
                <h1 class="text-xl md:text-2xl font-bold text-blue-700 dark:text-blue-200">Profile</h1>
                <div class="flex items-center gap-4">
                    <!-- Tombol Toggle Dark Mode -->
                    <button id="darkModeToggle" class="p-2 rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-gray-800 dark:text-blue-200 dark:hover:bg-gray-700" title="Toggle dark mode">
                        <svg id="darkModeIcon" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path id="sunIcon" class="block dark:hidden" stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M6.343 17.657l-1.414 1.414M17.657 17.657l-1.414-1.414M6.343 6.343L4.929 4.929M12 7a5 5 0 100 10 5 5 0 000-10z" />
                            <path id="moonIcon" class="hidden dark:block" stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
                        </svg>
                    </button>
                    <!-- Dropdown Profil User -->
                    <div class="relative">
                        <button id="profileDropdownBtn" class="flex items-center gap-2 focus:outline-none group">
                            <span class="font-semibold text-gray-700 hidden md:inline dark:text-gray-200"><?= htmlspecialchars(get_user_by_id($user_login['id'])['fullname']) ?></span>
                            <img id="profileImages" src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="w-9 h-9 md:w-10 md:h-10 rounded-full border-2 border-blue-400 shadow object-cover">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 dark:text-gray-300 dark:group-hover:text-blue-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <!-- Menu Dropdown Profil -->
                        <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg py-2 z-40 hidden">
                            <a href="profile.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-800">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                View Profile
                            </a>
                            <div class="border-t border-gray-100 dark:border-gray-700 my-2"></div>
                            <a href="functions/logout.php" class="flex items-center px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/40">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Main Content Profile -->
            <main class="flex-1 p-4 md:p-10">
                <!-- Notifikasi Toast Flash -->
                <?php if ($flash = getFlash("edit_profile")): ?>
                    <div id="toast-flash" style="position: fixed; top: 80px; right: 38px; z-index: 9999;">
                        <?= $flash ?>
                    </div>
                <?php endif; ?>
                <!-- Card Form Edit Profile -->
                <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-lg p-8 dark:bg-gray-900 dark:border dark:border-gray-800">
                    <!-- Form Edit Profile -->
                    <form class="flex flex-col items-center gap-4 w-full mt-0 space-y-0" method="post" enctype="multipart/form-data" action="functions/edit_profile.php">
                        <!-- Foto Profil User -->
                        <div class="relative group">
                            <img id="profileImage" src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="w-28 h-28 rounded-full border-4 border-blue-500 shadow-lg object-cover">
                            <!-- Tombol Upload Foto Profil -->
                            <label for="profilePicInput" class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 rounded-full cursor-pointer transition">
                                <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6-6m2 2l-6 6m-2 2H7a2 2 0 01-2-2v-2a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2z" />
                                </svg>
                                <input type="file" id="profilePicInput" name="profile_pic" class="hidden" accept="image/*">
                            </label>
                            <!-- Tombol Hapus Foto Profil -->
                            <?php if (!empty($user['profile_pic']) && file_exists('assets/profiles/' . $user['profile_pic'])): ?>
                                <button type="button" onclick="openDeletePhotoModal()" class="absolute bottom-0 right-0 bg-red-600 hover:bg-red-700 text-white rounded-full p-2 shadow-lg transition" title="Hapus Foto Profile">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                        <!-- Input Hidden User ID -->
                        <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_login['id']) ?>">
                        <!-- Input Nama Lengkap -->
                        <div class="w-full">
                            <label class="block text-gray-700 dark:text-gray-200 font-semibold mb-2" for="fullname">Nama Lengkap</label>
                            <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars(get_user_by_id($user_login['id'])['fullname']) ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" required>
                        </div>
                        <!-- Input Email (readonly) -->
                        <div class="w-full">
                            <label class="block text-gray-700 dark:text-gray-200 font-semibold mb-2" for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars(get_user_by_id($user_login['id'])['email']) ?>" class="w-full px-4 py-2 border rounded-lg bg-gray-100 cursor-not-allowed dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400" readonly>
                        </div>
                        <!-- Accordion Ubah Password -->
                        <div class="w-full border rounded-lg dark:border-gray-700">
                            <button type="button" onclick="document.getElementById('passwordSection').classList.toggle('hidden')" class="w-full flex items-center justify-between px-4 py-3 text-blue-600 font-semibold focus:outline-none dark:text-blue-300">
                                Ubah Password
                                <svg class="w-5 h-5 ml-2 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <!-- Section Input Password Baru -->
                            <div id="passwordSection" class="hidden px-4 pb-4 pt-2 space-y-3">
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-200 mb-1" for="current_password">Password Lama</label>
                                    <input type="password" id="current_password" name="current_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                </div>
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-200 mb-1" for="new_password">Password Baru</label>
                                    <input type="password" id="new_password" name="new_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                </div>
                                <div>
                                    <label class="block text-gray-700 dark:text-gray-200 mb-1" for="confirm_password">Konfirmasi Password Baru</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                </div>
                            </div>
                        </div>
                        <!-- Tombol Simpan & Hapus Akun -->
                        <div class="flex justify-end w-full gap-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition dark:bg-blue-700 dark:hover:bg-blue-800">Simpan Perubahan</button>
                            <button type="button" onclick="openDeleteModal()" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2 rounded-lg shadow transition dark:bg-red-700 dark:hover:bg-red-800">Hapus Akun</button>
                        </div>
                    </form>
                </div>

                <!-- Modal Konfirmasi Hapus Foto Profil -->
                <div id="deletePhotoModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg max-w-md w-full p-6">
                        <h2 class="text-xl font-bold text-red-600 mb-2 dark:text-red-400">Konfirmasi Hapus Foto Profil</h2>
                        <p class="mb-4 text-gray-700 dark:text-gray-200">
                            Anda yakin ingin menghapus foto profil Anda?<br>
                            <span class="font-semibold text-red-500">Aksi ini tidak dapat dibatalkan.</span>
                        </p>
                        <div class="flex justify-end gap-2 mt-4">
                            <button type="button" onclick="closeDeletePhotoModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                            <form method="post" action="functions/delete_profile_pic.php" class="inline">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_login['id']) ?>">
                                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold">Hapus Foto</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal Konfirmasi Hapus Akun -->
                <div id="deleteAccountModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg max-w-md w-full p-6">
                        <h2 class="text-xl font-bold text-red-600 mb-2 dark:text-red-400">Konfirmasi Hapus Akun</h2>
                        <p class="mb-4 text-gray-700 dark:text-gray-200">
                            Apakah Anda yakin ingin menghapus akun Anda? <br>
                            <span class="font-semibold text-red-500">Tindakan ini tidak dapat dibatalkan.</span><br>
                            Setelah dihapus, semua data akun Anda akan hilang secara permanen dan Anda akan otomatis logout.
                        </p>
                        <div class="flex justify-end gap-2 mt-4">
                            <button onclick="closeDeleteModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                            <form method="post" action="functions/delete_account.php">
                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_login['id']) ?>">
                                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold">Hapus Akun</button>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
    <script src="assets/js/profile.js"></script>
</body>

</html>