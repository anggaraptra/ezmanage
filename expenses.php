<?php
require_once 'functions/functions.php';
cek_session();

// Dummy data, ganti dengan query database Anda
$user_login = $_SESSION['user'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzManage - Expenses</title>
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
                <a href="dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">
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
                <a href="expenses.php" class="flex items-center gap-2 px-3 py-2 rounded bg-white bg-opacity-10 hover:bg-opacity-20 transition">
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
                <h1 class="text-2xl font-bold text-blue-600">Pengeluaran</h1>
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
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-700">Halo, <?= htmlspecialchars($user_login['fullname']) ?>!</h2>
                    <p class="text-gray-500">Selamat datang di halaman Pengeluaran! Di sini Anda dapat mencatat dan memantau pengeluaran Anda.</p>
                </div>
                <!-- Expenses -->
                <div class="bg-white rounded-lg shadow p-6">
                    <!-- Form Tambah Pengeluaran -->
                    <div class="mb-8">
                        <form action="" method="post" class="flex flex-wrap gap-4 items-end">
                            <div>
                                <label class="block text-gray-600 mb-1">Jumlah</label>
                                <input type="number" name="amount" min="0" step="0.01" required class="border rounded px-3 py-2 w-32">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Tanggal</label>
                                <input type="date" name="date" required class="border rounded px-3 py-2 w-40">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Kategori</label>
                                <select name="category" required class="border rounded px-3 py-2 w-40">
                                    <option value="">Pilih Kategori</option>
                                    <?php
                                    // Dummy kategori, ganti dengan query dari database
                                    $categories = ['Makanan', 'Transportasi', 'Tagihan', 'Hiburan', 'Lainnya'];
                                    foreach ($categories as $cat) {
                                        echo "<option value=\"" . htmlspecialchars($cat) . "\">" . htmlspecialchars($cat) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Deskripsi</label>
                                <input type="text" name="description" class="border rounded px-3 py-2 w-56">
                            </div>
                            <div>
                                <label class="block text-gray-600 mb-1">Mata Uang</label>
                                <select name="currency" required class="border rounded px-3 py-2 w-32">
                                    <?php
                                    // Daftar mata uang populer
                                    $currencies = [
                                        'IDR' => 'Rupiah (IDR)',
                                        'USD' => 'Dollar AS (USD)',
                                        'EUR' => 'Euro (EUR)',
                                        'SGD' => 'Dollar Singapura (SGD)',
                                        'JPY' => 'Yen Jepang (JPY)',
                                        'MYR' => 'Ringgit Malaysia (MYR)',
                                    ];
                                    $selected_currency = $_POST['currency'] ?? 'IDR';
                                    foreach ($currencies as $code => $label) {
                                        $selected = $selected_currency === $code ? 'selected' : '';
                                        echo "<option value=\"$code\" $selected>$label</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" name="add_expense" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah</button>
                        </form>
                        <?php
                        // Simulasi proses tambah pengeluaran
                        if (isset($_POST['add_expense'])) {
                            // Validasi dan simpan ke database di sini
                            echo '<div class="mt-3 text-green-600">Pengeluaran berhasil ditambahkan (simulasi).</div>';
                        }
                        ?>
                    </div>

                    <!-- Daftar Pengeluaran -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3">Daftar Pengeluaran</h3>
                        <table class="min-w-full bg-white border rounded shadow text-sm">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="py-2 px-3 border-b">Tanggal</th>
                                    <th class="py-2 px-3 border-b">Kategori</th>
                                    <th class="py-2 px-3 border-b">Deskripsi</th>
                                    <th class="py-2 px-3 border-b">Jumlah</th>
                                    <th class="py-2 px-3 border-b">Mata Uang</th>
                                    <th class="py-2 px-3 border-b">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Dummy data pengeluaran, ganti dengan query database
                                $expenses = [
                                    ['id' => 1, 'date' => '2024-06-01', 'category' => 'Makanan', 'description' => 'Sarapan', 'amount' => 25000, 'currency' => 'IDR'],
                                    ['id' => 2, 'date' => '2024-06-02', 'category' => 'Transportasi', 'description' => 'Ojek ke kantor', 'amount' => 2, 'currency' => 'USD'],
                                    ['id' => 3, 'date' => '2024-06-03', 'category' => 'Tagihan', 'description' => 'Listrik', 'amount' => 100, 'currency' => 'MYR'],
                                ];
                                $total = [];
                                foreach ($expenses as $exp) {
                                    $curr = $exp['currency'];
                                    if (!isset($total[$curr])) $total[$curr] = 0;
                                    $total[$curr] += $exp['amount'];
                                    echo "<tr>
                                        <td class='py-2 px-3 border-b'>" . htmlspecialchars($exp['date']) . "</td>
                                        <td class='py-2 px-3 border-b'>" . htmlspecialchars($exp['category']) . "</td>
                                        <td class='py-2 px-3 border-b'>" . htmlspecialchars($exp['description']) . "</td>
                                        <td class='py-2 px-3 border-b text-right'>" . number_format($exp['amount'], 2, ',', '.') . "</td>
                                        <td class='py-2 px-3 border-b'>" . htmlspecialchars($exp['currency']) . "</td>
                                        <td class='py-2 px-3 border-b'>
                                            <a href='#' class='text-blue-600 hover:underline mr-2'>Edit</a>
                                            <a href='#' class='text-red-600 hover:underline'>Hapus</a>
                                        </td>
                                    </tr>";
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <?php foreach ($total as $curr => $amt): ?>
                                    <tr class="bg-blue-100 font-semibold">
                                        <td colspan="3" class="py-2 px-3 text-right">Total (<?= htmlspecialchars($curr) ?>)</td>
                                        <td class="py-2 px-3 text-right"><?= number_format($amt, 2, ',', '.') ?></td>
                                        <td class="py-2 px-3"><?= htmlspecialchars($curr) ?></td>
                                        <td></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Manajemen Kategori -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3">Manajemen Kategori</h3>
                        <form action="" method="post" class="flex gap-2 mb-3">
                            <input type="text" name="new_category" placeholder="Kategori baru" class="border rounded px-3 py-2 w-48">
                            <button type="submit" name="add_category" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah Kategori</button>
                        </form>
                        <?php
                        if (isset($_POST['add_category'])) {
                            // Simulasi tambah kategori
                            echo '<div class="text-green-600 mb-2">Kategori berhasil ditambahkan (simulasi).</div>';
                        }
                        ?>
                        <ul class="list-disc pl-6">
                            <?php foreach ($categories as $cat): ?>
                                <li class="mb-1 flex items-center justify-between w-64">
                                    <span><?= htmlspecialchars($cat) ?></span>
                                    <span>
                                        <a href="#" class="text-blue-600 hover:underline mr-2">Edit</a>
                                        <a href="#" class="text-red-600 hover:underline">Hapus</a>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Laporan Pengeluaran -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3">Laporan Pengeluaran</h3>
                        <?php
                        // Simulasi laporan bulanan multi mata uang
                        $monthly_report = [
                            '2024-06' => ['IDR' => 140000, 'USD' => 10],
                            '2024-05' => ['IDR' => 120000, 'USD' => 5],
                            '2024-04' => ['IDR' => 95000],
                        ];
                        ?>
                        <table class="min-w-full bg-white border rounded shadow text-sm mb-4">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="py-2 px-3 border-b">Bulan</th>
                                    <th class="py-2 px-3 border-b">Total Pengeluaran</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($monthly_report as $month => $amounts): ?>
                                    <tr>
                                        <td class="py-2 px-3 border-b"><?= htmlspecialchars($month) ?></td>
                                        <td class="py-2 px-3 border-b text-right">
                                            <?php
                                            $arr = [];
                                            foreach ($amounts as $curr => $amt) {
                                                $arr[] = htmlspecialchars($curr) . ' ' . number_format($amt, 2, ',', '.');
                                            }
                                            echo implode(' | ', $arr);
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="text-gray-500 text-sm">* Data laporan di atas adalah simulasi dan mendukung multi mata uang.</div>
                    </div>

                    <!-- Fitur Opsional (Simulasi) -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-3">Fitur Opsional</h3>
                        <ul class="list-disc pl-6 text-gray-700">
                            <li>Pengingat pembayaran: <span class="text-green-600">Aktif (simulasi, notifikasi akan muncul di aplikasi)</span></li>
                            <li>Impor/Ekspor data: <span class="text-green-600">Tersedia (simulasi, tombol di bawah)</span></li>
                            <li>Mata uang:
                                <span class="text-green-600">
                                    <?= implode(', ', array_map(function ($c) {
                                        return htmlspecialchars($c);
                                    }, array_keys($currencies))) ?> (simulasi, dapat diubah di pengaturan)
                                </span>
                            </li>
                        </ul>
                        <div class="mt-3 flex gap-2">
                            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Impor CSV</button>
                            <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Ekspor CSV</button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="assets/js/expenses.js"></script>
</body>

</html>