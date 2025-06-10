<?php
require_once 'functions/functions.php';
cek_session();

// Ambil data user dari session
$user_login = $_SESSION['user'];

// Ambil data user
$user = get_user_by_id($user_login['id']);
// Cek apakah user sudah upload foto profile
$profilePic = !empty($user['profile_pic']) && file_exists('assets/profiles/' . $user['profile_pic'])
    ? 'assets/profiles/' . $user['profile_pic']
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']) . '&background=4f8ef7&color=fff';

// Ambil data todo user
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

// Ambil data pengeluaran user
$expenses = [];
$expense_result = dbquery("SELECT * FROM expenses WHERE user_id = " . intval($user_login['id']));
while ($row = mysqli_fetch_assoc($expense_result)) {
    $expenses[] = $row;
}

// Total pengeluaran
$total_expense = array_sum(array_column($expenses, 'amount'));

// Jumlah transaksi
$total_transactions = count($expenses);

// Pengeluaran terbesar & terkecil
$max_expense = $expenses ? max(array_column($expenses, 'amount')) : 0;
$min_expense = $expenses ? min(array_column($expenses, 'amount')) : 0;

// Data chart pengeluaran per bulan (8 bulan terakhir)
$expense_chart_labels = [];
$expense_chart_data = [];
for ($i = 7; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M', strtotime($month));
    $expense_chart_labels[] = $label;
    $sum = 0;
    foreach ($expenses as $exp) {
        if (strpos($exp['date'], $month) === 0) {
            $sum += $exp['amount'];
        }
    }
    $expense_chart_data[] = $sum;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='blue' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E">
    <title>EzManage - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>

<body class="bg-gradient-to-br from-blue-50 to-white min-h-screen font-sans dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-20 md:w-60 bg-white border-r border-blue-100 flex flex-col py-6 px-2 md:px-6 shadow-lg fixed inset-y-0 left-0 z-30  dark:bg-gray-900 dark:border-gray-800">
            <div class="mb-10 flex items-center justify-center md:justify-start gap-3">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="hidden md:inline text-2xl font-bold tracking-wide text-blue-700 dark:text-blue-200">EzManage</span>
            </div>
            <nav class="flex flex-col gap-2 mt-4">
                <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-blue-700 bg-blue-100 font-medium hover:bg-blue-200  dark:text-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                    </svg>
                    <span class="hidden md:inline">Dashboard</span>
                </a>
                <a href="todo.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700  dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <span class="hidden md:inline">Todo List</span>
                </a>
                <a href="expenses.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700  dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                    </svg>
                    <span class="hidden md:inline">Pengeluaran</span>
                </a>
                <a href="calculations.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700  dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v2M8 2v2M3 6h18M4 10h16M4 14h16M4 18h16" />
                        <rect x="8" y="14" width="8" height="6" rx="2" />
                    </svg>
                    <span class="hidden md:inline">Kalkulator</span>
                </a>
            </nav>
            <div class="mt-auto pt-8 border-t border-blue-100 dark:border-gray-800">
                <a href="functions/logout.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-600  dark:text-red-400 dark:hover:bg-red-900/40 dark:hover:text-red-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                    </svg>
                    <span class="hidden md:inline">Logout</span>
                </a>
            </div>
        </aside>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col md:ml-60 ml-20 ">
            <!-- Navbar -->
            <header class="bg-white/80 backdrop-blur shadow-sm flex items-center justify-between px-4 md:px-10 py-4 sticky top-0 z-20 dark:bg-gray-900/80 dark:shadow-gray-900/30">
                <h1 class="text-xl md:text-2xl font-bold text-blue-700 dark:text-blue-200">Dashboard</h1>
                <div class="flex items-center gap-4">
                    <!-- Dark mode toggle -->
                    <button id="darkModeToggle" class="p-2 rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-gray-800 dark:text-blue-200 dark:hover:bg-gray-700 " title="Toggle dark mode">
                        <svg id="darkModeIcon" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path id="sunIcon" class="block dark:hidden" stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2m9-9h-2M5 12H3m15.364-6.364l-1.414 1.414M6.343 17.657l-1.414 1.414M17.657 17.657l-1.414-1.414M6.343 6.343L4.929 4.929M12 7a5 5 0 100 10 5 5 0 000-10z" />
                            <path id="moonIcon" class="hidden dark:block" stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3a7 7 0 109.79 9.79z" />
                        </svg>
                    </button>
                    <div class="relative">
                        <button id="profileDropdownBtn" class="flex items-center gap-2 focus:outline-none group">
                            <span class="font-semibold text-gray-700 hidden md:inline dark:text-gray-200"><?= htmlspecialchars(get_user_by_id($user_login['id'])['fullname']) ?></span>
                            <img id="profileImage" src="<?= htmlspecialchars($profilePic) ?>" alt="Profile" class="w-9 h-9 md:w-10 md:h-10 rounded-full border-2 border-blue-400 shadow object-cover">
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 dark:text-gray-300 dark:group-hover:text-blue-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="profileDropdownMenu" class="hidden absolute right-0 mt-2 w-40 bg-white rounded shadow-lg border z-30 dark:bg-gray-900 dark:border-gray-800">
                            <a href="profile.php" class="block px-4 py-2 text-gray-700 hover:bg-blue-50 dark:text-gray-200 dark:hover:bg-blue-900/40">View Profile</a>
                        </div>
                    </div>
                </div>
            </header>
            <!-- Content -->
            <main class="flex-1 p-4 md:p-10">
                <!-- Welcome -->
                <div class="mb-8">
                    <h2 class="text-lg md:text-xl font-semibold text-blue-800 mb-1 dark:text-blue-200">Selamat datang di <span class="text-blue-600 dark:text-blue-400">EzManage</span> 👋</h2>
                    <p class="text-gray-500 dark:text-gray-300">Kelola aktivitas harianmu dengan mudah dan efisien.</p>
                </div>
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Todo Summary -->
                    <div class="bg-white rounded-xl shadow-md p-5 flex flex-col items-start relative overflow-hidden border border-blue-100 dark:bg-gray-900 dark:border-gray-800">
                        <div class="absolute right-4 top-4 opacity-10 text-blue-400 dark:text-blue-700">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div class="text-xs text-blue-500 mb-1 flex items-center gap-1 font-medium dark:text-blue-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Todo
                        </div>
                        <div class="flex items-end gap-2 mb-2">
                            <span class="text-3xl font-bold text-blue-700 dark:text-blue-200"><?= count($todos) ?></span>
                            <span class="text-xs text-gray-400 mb-1 dark:text-gray-300">total tugas</span>
                        </div>
                        <div class="w-full flex gap-2 mb-2">
                            <span class="flex-1 flex flex-col items-center bg-red-50 text-red-600 px-2 py-1 rounded dark:bg-red-900/40 dark:text-red-400">
                                <span class="font-semibold"><?= count(array_filter($todos, fn($t) => $t['status'] == 'Belum')) ?></span>
                                <span class="text-xs">Belum</span>
                            </span>
                            <span class="flex-1 flex flex-col items-center bg-yellow-50 text-yellow-700 px-2 py-1 rounded dark:bg-yellow-900/40 dark:text-yellow-300">
                                <span class="font-semibold"><?= count(array_filter($todos, fn($t) => $t['status'] == 'Proses')) ?></span>
                                <span class="text-xs">Proses</span>
                            </span>
                            <span class="flex-1 flex flex-col items-center bg-green-50 text-green-700 px-2 py-1 rounded dark:bg-green-900/40 dark:text-green-300">
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
                        ?>
                        <div class="w-full mt-2">
                            <div class="h-2 bg-blue-100 rounded-full overflow-hidden mb-1 dark:bg-blue-900/40">
                                <?php
                                $total = count($todos);
                                $done = count($selesai);
                                $percent = $total ? round($done / $total * 100) : 0;
                                ?>
                                <div class="h-full bg-blue-500 rounded-full  dark:bg-blue-400" style="width: <?= $percent ?>%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-400 dark:text-gray-300">
                                <span><?= $percent ?>% selesai</span>
                                <span><?= $done ?> dari <?= $total ?> tugas</span>
                            </div>
                        </div>
                        <div class="w-full mt-3 text-xs text-gray-600 dark:text-gray-200">
                            <?php if ($nearest): ?>
                                <div class="flex items-center gap-2 mb-1">
                                    <svg class="w-4 h-4 text-red-400 dark:text-red-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Tugas terdekat: <span class="font-semibold text-blue-700 dark:text-blue-200"><?= htmlspecialchars($nearest['title']) ?></span> (<?= htmlspecialchars($nearest['due_date']) ?>)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <a href="todo.php" class="mt-4 text-xs text-blue-500 hover:underline flex items-center gap-1 dark:text-blue-300">
                            Lihat detail
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    <!-- Expense Summary -->
                    <div class="bg-white rounded-xl shadow-md p-5 flex flex-col items-start relative overflow-hidden border border-blue-100 dark:bg-gray-900 dark:border-gray-800">
                        <div class="absolute right-4 top-4 opacity-10 text-blue-400 dark:text-blue-700">
                            <svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                            </svg>
                        </div>
                        <div class="text-xs text-blue-500 mb-1 flex items-center gap-1 font-medium dark:text-blue-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                            </svg>
                            Pengeluaran
                        </div>
                        <div class="text-2xl font-bold text-blue-700 mb-2 dark:text-blue-200">
                            Rp<?= number_format($total_expense, 0, ',', '.') ?>
                        </div>
                        <div class="flex gap-4 mb-2">
                            <div class="flex flex-col items-start">
                                <span class="text-xs text-gray-400 dark:text-gray-300">Transaksi</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-200"><?= $total_transactions ?></span>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="text-xs text-gray-400 dark:text-gray-300">Terbesar</span>
                                <span class="font-semibold text-green-600 dark:text-green-300">Rp<?= number_format($max_expense, 0, ',', '.') ?></span>
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="text-xs text-gray-400 dark:text-gray-300">Terkecil</span>
                                <span class="font-semibold text-red-600 dark:text-red-300">Rp<?= number_format($min_expense, 0, ',', '.') ?></span>
                            </div>
                        </div>
                        <!-- Chart Pengeluaran -->
                        <div class="w-full my-2">
                            <canvas id="expenseChart" height="60"></canvas>
                        </div>
                        <a href="expenses.php" class="mt-3 text-xs text-blue-500 hover:underline flex items-center gap-1 dark:text-blue-300">
                            Lihat detail
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                    <!-- Clock & Calendar -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-400 rounded-xl shadow-md flex flex-col items-center justify-center p-6 text-white relative overflow-hidden w-full border border-blue-100 dark:from-blue-900 dark:to-blue-800 dark:border-gray-800">
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
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Inisialisasi chart pengeluaran
        const expenseLabels = <?= json_encode($expense_chart_labels) ?>;
        const expenseData = <?= json_encode($expense_chart_data) ?>;
        const ctx = document.getElementById('expenseChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: expenseLabels,
                datasets: [{
                    label: 'Pengeluaran',
                    data: expenseData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#3b82f6'
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        display: true,
                        grid: {
                            color: '#f3f4f6'
                        },
                        beginAtZero: true
                    }
                }
            }
        });

        // Dark mode toggle logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
        // Initial check
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
        darkModeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            if (html.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    </script>
    <script src="assets/js/dashboard.js"></script>
</body>

</html>