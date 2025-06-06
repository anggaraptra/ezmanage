<?php
require_once 'functions/functions.php';
cek_session();

// Ambil data user dari session
$user_login = $_SESSION['user'];
$todos = [];
$result = dbquery("SELECT * FROM todos WHERE user_id = " . intval($user_login['id']));
while ($row = mysqli_fetch_assoc($result)) {
    $todos[] = $row;
}

// Ambil filter dari request
$status_filter = $_GET['status'] ?? '';
$due_filter = $_GET['due_date'] ?? '';
$search = $_GET['search'] ?? '';

// Filter data
$filtered_todos = array_filter($todos, function ($todo) use ($status_filter, $due_filter, $search) {
    $status_ok = !$status_filter || $todo['status'] === $status_filter;
    $due_ok = !$due_filter || $todo['due_date'] === $due_filter;
    $search_ok = !$search || stripos($todo['title'], $search) !== false || stripos($todo['description'], $search) !== false;
    return $status_ok && $due_ok && $search_ok;
});

// Ambil data histori kalkulator dari database
$calc_history = [];
$calc_result = dbquery("SELECT * FROM calculations WHERE user_id = " . intval($user_login['id']) . " ORDER BY created_at DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($calc_result)) {
    $calc_history[] = $row;
}

// Ambil total penggunaan kalkulator
$total_calc_all = dbquery("SELECT COUNT(*) as total FROM calculations WHERE user_id = " . intval($user_login['id']));
$total_calc_count = mysqli_fetch_assoc($total_calc_all)['total'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzManage - Dashboard</title>
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
                <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded bg-white bg-opacity-10 hover:bg-opacity-20 transition">
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
                <h1 class="text-2xl font-bold text-blue-600">Dashboard</h1>
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
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Selamat datang di EzManage 👋</h2>
                    <p class="text-gray-500">Kelola aktivitas harianmu dengan mudah dan efisien.</p>
                </div>

                <!-- Sekilas Ringkasan -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Ringkasan Todo -->
                    <div class="bg-white rounded-lg shadow p-5 flex flex-col items-start relative overflow-hidden">
                        <div class="absolute right-4 top-4 opacity-10 text-blue-400">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-500 mb-1 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Todo
                        </div>
                        <div class="flex items-end gap-2 mb-2">
                            <span class="text-3xl font-bold text-blue-600"><?= count($todos) ?></span>
                            <span class="text-xs text-gray-400 mb-1">total tugas</span>
                        </div>
                        <div class="w-full flex gap-2 mb-2">
                            <span class="flex-1 flex flex-col items-center bg-red-100 text-red-700 px-2 py-1 rounded">
                                <span class="font-semibold"><?= count(array_filter($todos, fn($t) => $t['status'] == 'Belum')) ?></span>
                                <span class="text-xs">Belum</span>
                            </span>
                            <span class="flex-1 flex flex-col items-center bg-yellow-100 text-yellow-700 px-2 py-1 rounded">
                                <span class="font-semibold"><?= count(array_filter($todos, fn($t) => $t['status'] == 'Proses')) ?></span>
                                <span class="text-xs">Proses</span>
                            </span>
                            <span class="flex-1 flex flex-col items-center bg-green-100 text-green-700 px-2 py-1 rounded">
                                <span class="font-semibold"><?= count(array_filter($todos, fn($t) => $t['status'] == 'Selesai')) ?></span>
                                <span class="text-xs">Selesai</span>
                            </span>
                        </div>
                        <?php
                        $belum = array_filter($todos, fn($t) => $t['status'] == 'Belum');
                        $proses = array_filter($todos, fn($t) => $t['status'] == 'Proses');
                        $selesai = array_filter($todos, fn($t) => $t['status'] == 'Selesai');
                        $nearest = null;
                        if (!empty($belum)) {
                            usort($belum, fn($a, $b) => strcmp($a['due_date'], $b['due_date']));
                            $nearest = $belum[0];
                        }
                        $latest = null;
                        if (!empty($todos)) {
                            usort($todos, fn($a, $b) => strcmp($b['due_date'], $a['due_date']));
                            $latest = $todos[0];
                        }
                        ?>
                        <div class="w-full mt-2">
                            <div class="h-2 bg-blue-100 rounded-full overflow-hidden mb-1">
                                <?php
                                $total = count($todos);
                                $done = count($selesai);
                                $percent = $total ? round($done / $total * 100) : 0;
                                ?>
                                <div class="h-full bg-blue-500 rounded-full transition-all duration-300" style="width: <?= $percent ?>%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400">
                                <span><?= $percent ?>% selesai</span>
                                <span><?= $done ?> dari <?= $total ?> tugas</span>
                            </div>
                        </div>
                        <div class="w-full mt-3 text-xs text-gray-600">
                            <?php if ($nearest): ?>
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Tugas terdekat: <span class="font-semibold text-blue-700"><?= htmlspecialchars($nearest['title']) ?></span> (<?= htmlspecialchars($nearest['due_date']) ?>)</span>
                                </div>
                            <?php endif; ?>
                            <?php if ($latest): ?>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                    <span>Tugas terakhir: <span class="font-semibold"><?= htmlspecialchars($latest['title']) ?></span> (<?= htmlspecialchars($latest['due_date']) ?>)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="todo.php" class="mt-4 text-xs text-blue-500 hover:underline flex items-center gap-1">
                            Lihat detail
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    <!-- Ringkasan Pengeluaran -->
                    <div class="bg-white rounded-lg shadow p-5 flex flex-col items-start relative overflow-hidden">
                        <div class="absolute right-4 top-4 opacity-10 text-blue-400">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-500 mb-1 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                            </svg>
                            Total Pengeluaran
                        </div>
                        <div class="text-2xl font-bold text-blue-600 mb-2">
                            Rp<?= number_format(1250000, 0, ',', '.') ?>
                        </div>
                        <div class="flex gap-4 mb-2">
                            <div class="flex flex-col items-start">
                                <span class="text-xs text-gray-400">Transaksi</span>
                                <span class="font-semibold text-gray-700">8</span>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="text-xs text-gray-400">Terbesar</span>
                                <span class="font-semibold text-green-600">Rp<?= number_format(500000, 0, ',', '.') ?></span>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="text-xs text-gray-400">Terkecil</span>
                                <span class="font-semibold text-red-600">Rp<?= number_format(25000, 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <div class="w-full mt-2">
                            <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full" style="width: 65%"></div>
                            </div>
                            <div class="text-xs text-gray-400 mt-1">65% dari anggaran bulanan</div>
                        </div>
                        <a href="expenses.php" class="mt-3 text-xs text-blue-500 hover:underline flex items-center gap-1">
                            Lihat detail
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    <!-- Ringkasan Kalkulator -->
                    <div class="bg-white rounded-lg shadow p-5 flex flex-col items-start relative overflow-hidden">
                        <div class="absolute right-4 top-4 opacity-10 text-blue-400">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <rect x="8" y="14" width="8" height="6" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v2M8 2v2M3 6h18M4 10h16M4 14h16M4 18h16" />
                            </svg>
                        </div>
                        <div class="text-sm text-gray-500 mb-1 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="8" y="14" width="8" height="6" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v2M8 2v2M3 6h18" />
                            </svg>
                            Kalkulator
                        </div>
                        <div class="text-2xl font-bold text-blue-600 mb-2">
                            <?= $total_calc_count ?> kali digunakan
                        </div>
                        <div class="w-full mb-2">
                            <span class="text-xs text-gray-400">Histori Terakhir:</span>
                            <ul class="mt-1 space-y-1">
                                <?php if ($calc_history): ?>
                                    <?php foreach ($calc_history as $calc): ?>
                                        <li class="text-xs text-gray-700 flex items-center gap-2">
                                            <span class="inline-block bg-blue-100 text-blue-700 px-2 py-0.5 rounded"><?= htmlspecialchars($calc['calculation']) ?></span>
                                            =
                                            <span class="font-semibold">
                                                <?php
                                                $result = $calc['result'];
                                                if (is_numeric($result)) {
                                                    // Jika integer, tampilkan tanpa desimal
                                                    if (intval($result) == floatval($result)) {
                                                        echo intval($result);
                                                    } else {
                                                        echo $result;
                                                    }
                                                } else {
                                                    echo htmlspecialchars($result);
                                                }
                                                ?>
                                            </span>
                                            <span class="text-gray-400">(<?= date('d/m/Y H:i', strtotime($calc['created_at'])) ?>)</span>
                                        </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="text-xs text-gray-400">Belum ada histori</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <a href="calculations.php" class="mt-3 text-xs text-blue-500 hover:underline flex items-center gap-1">
                            Lihat detail
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Tampilan jam & kalender lengkap -->
                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                    <!-- Jam & Kalender Lengkap -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-400 rounded-lg shadow flex flex-col items-center justify-center p-6 text-white relative overflow-hidden w-full">
                        <div class="absolute right-4 top-4 opacity-10">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            </svg>
                        </div>
                        <div class="text-base font-semibold mb-1 flex items-center gap-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            </svg>
                            Jam & Kalender
                        </div>
                        <div id="digitalClock" class="text-4xl font-bold tracking-widest drop-shadow-lg mb-2"></div>
                        <div id="miniCalendar" class="w-full mt-2"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="assets/js/dashboard.js"></script>
</body>

</html>