<?php
require_once 'functions/functions.php';
cek_session();

// Ambil data user dari session
$user_login = $_SESSION['user'];

// Ambil data todo dari database
$todos = [];
$result = dbquery("SELECT * FROM todos WHERE user_id = " . intval($user_login['id']));
while ($row = mysqli_fetch_assoc($result)) {
    $todos[] = $row;
}

// Ambil filter dari request
$status_filter = $_GET['status'] ?? '';
$due_filter = $_GET['due_date'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$search = $_GET['search'] ?? '';

// Filter data
$filtered_todos = array_filter($todos, function ($todo) use ($status_filter, $due_filter, $priority_filter, $search) {
    $status_ok = !$status_filter || $todo['status'] === $status_filter;
    $due_ok = !$due_filter || $todo['due_date'] === $due_filter;
    $priority_ok = !$priority_filter || (isset($todo['priority']) && $todo['priority'] === $priority_filter);
    $search_ok = !$search || stripos($todo['title'], $search) !== false || stripos($todo['description'], $search) !== false;
    return $status_ok && $due_ok && $priority_ok && $search_ok;
});

// Ambil data user
$user = get_user_by_id($user_login['id']);
// Cek apakah user sudah upload foto profile
$profilePic = !empty($user['profile_pic']) && file_exists('assets/profiles/' . $user['profile_pic'])
    ? 'assets/profiles/' . $user['profile_pic']
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']) . '&background=4f8ef7&color=fff';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='blue' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E">
    <title>EzManage - Todo List</title>
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
        <aside class="w-20 md:w-60 bg-white border-r border-blue-100 flex flex-col py-6 px-2 md:px-6 shadow-lg fixed inset-y-0 left-0 z-30 dark:bg-gray-900 dark:border-gray-800">
            <div class="mb-10 flex items-center justify-center md:justify-start gap-3">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="hidden md:inline text-2xl font-bold tracking-wide text-blue-700 dark:text-blue-200">EzManage</span>
            </div>
            <nav class="flex flex-col gap-2 mt-4">
                <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h18" />
                    </svg>
                    <span class="hidden md:inline">Dashboard</span>
                </a>
                <a href="todo.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-blue-700 bg-blue-100 font-medium hover:bg-blue-200 dark:text-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60">
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
            <div class="mt-auto pt-8 border-t border-blue-100 dark:border-gray-800">
                <a href="functions/logout.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-red-500 hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-900/40 dark:hover:text-red-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                    </svg>
                    <span class="hidden md:inline">Logout</span>
                </a>
            </div>
        </aside>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col md:ml-60 ml-20">
            <!-- Navbar -->
            <header class="bg-white/80 backdrop-blur shadow-sm flex items-center justify-between px-4 md:px-10 py-4 sticky top-0 z-20 dark:bg-gray-900/80 dark:shadow-gray-900/30">
                <h1 class="text-xl md:text-2xl font-bold text-blue-700 dark:text-blue-200">Todo List</h1>
                <div class="flex items-center gap-4">
                    <!-- Dark mode toggle -->
                    <button id="darkModeToggle" class="p-2 rounded-full bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-gray-800 dark:text-blue-200 dark:hover:bg-gray-700" title="Toggle dark mode">
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
                <div class="mb-6">
                    <h2 class="text-lg md:text-xl font-semibold text-blue-800 mb-1 dark:text-blue-200">Halo, <?= htmlspecialchars(get_user_by_id($user_login['id'])['fullname']) ?>!</h2>
                    <p class="text-gray-500 dark:text-gray-300">Selamat datang di halaman Todo List! Semangat menyelesaikan tugas-tugasmu hari ini!</p>
                </div>
                <?php
                // Urutkan: 1) status != Selesai, 2) due_date terdekat ke hari ini di atas, 3) status Selesai di bawah
                usort($filtered_todos, function ($a, $b) {
                    if ($a['status'] === 'Selesai' && $b['status'] !== 'Selesai') return 1;
                    if ($a['status'] !== 'Selesai' && $b['status'] === 'Selesai') return -1;
                    $dateA = strtotime($a['due_date']);
                    $dateB = strtotime($b['due_date']);
                    return $dateA <=> $dateB;
                });
                ?>
                <!-- Todo List -->
                <div class="bg-white rounded-xl shadow-md p-6 border border-blue-100 dark:bg-gray-900 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-blue-600 dark:text-blue-300">Todo List</h3>
                        <a href="#" onclick="openModal('add')" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-800">+ Tambah Todo</a>
                    </div>
                    <!-- Filter & Search Form -->
                    <form method="get" class="flex flex-wrap gap-3 mb-4 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 dark:text-gray-300">Status</label>
                            <select name="status" class="border rounded-lg px-2 py-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                                <option value="">Semua</option>
                                <option value="Belum" <?= $status_filter == 'Belum' ? 'selected' : '' ?>>Belum</option>
                                <option value="Proses" <?= $status_filter == 'Proses' ? 'selected' : '' ?>>Proses</option>
                                <option value="Selesai" <?= $status_filter == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 dark:text-gray-300">Prioritas</label>
                            <select name="priority" class="border rounded-lg px-2 py-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                                <option value="">Semua</option>
                                <option value="Tinggi" <?= $priority_filter == 'Tinggi' ? 'selected' : '' ?>>Tinggi</option>
                                <option value="Sedang" <?= $priority_filter == 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                                <option value="Rendah" <?= $priority_filter == 'Rendah' ? 'selected' : '' ?>>Rendah</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 dark:text-gray-300">Jatuh Tempo</label>
                            <input type="date" name="due_date" value="<?= htmlspecialchars($due_filter) ?>" class="border rounded-lg px-2 py-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1 dark:text-gray-300">Cari</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Judul/Deskripsi" class="border rounded-lg px-2 py-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                        </div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-800">Filter</button>
                        <a href="todo.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Reset</a>
                    </form>
                    <?php
                    $reminders = [];
                    $late_reminders = [];
                    $now = new DateTime('now');
                    $today_midnight = (clone $now)->setTime(0, 0, 0);
                    foreach ($filtered_todos as $todo) {
                        if ($todo['status'] !== 'Selesai') {
                            if (preg_match('/\d{4}-\d{2}-\d{2}/', $todo['due_date'], $matches)) {
                                $due_date_only = $matches[0];
                                $due = DateTime::createFromFormat('Y-m-d', $due_date_only);
                                if ($due) {
                                    $due->setTime(0, 0, 0);
                                    $interval = $today_midnight->diff($due);
                                    $diff_days = (int)$interval->format('%r%a');
                                    if ($diff_days >= 0 && $diff_days <= 3) {
                                        $reminders[] = [
                                            'todo' => $todo,
                                            'diff' => $diff_days,
                                            'due' => $due
                                        ];
                                    }
                                    if ($diff_days < 0) {
                                        $late_reminders[] = [
                                            'todo' => $todo,
                                            'diff' => $diff_days,
                                            'due' => $due
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    if (!empty($late_reminders)): ?>
                        <div class="mb-4">
                            <?php foreach ($late_reminders as $remind): ?>
                                <div class="flex items-center gap-2 bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-2 rounded-lg mb-2 dark:bg-red-900/40 dark:border-red-500 dark:text-red-300">
                                    <svg class="w-5 h-5 text-red-400 dark:text-red-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                    </svg>
                                    <span>
                                        <strong><?= htmlspecialchars($remind['todo']['title']) ?></strong>
                                        <b>terlambat</b>! (Jatuh tempo: <?= htmlspecialchars($remind['due']->format('d-m-Y')) ?>)
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($reminders)): ?>
                        <div class="mb-4">
                            <?php foreach ($reminders as $remind): ?>
                                <div class="flex items-center gap-2 bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 px-4 py-2 rounded-lg mb-2 dark:bg-yellow-900/40 dark:border-yellow-400 dark:text-yellow-200">
                                    <svg class="w-5 h-5 text-yellow-400 dark:text-yellow-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                                    </svg>
                                    <span>
                                        <strong><?= htmlspecialchars($remind['todo']['title']) ?></strong>
                                        <?php
                                        if ($remind['diff'] == 0) {
                                            echo 'jatuh tempo <b>hari ini</b>!';
                                        } elseif ($remind['diff'] == 1) {
                                            echo 'jatuh tempo <b>besok</b>!';
                                        } else {
                                            echo 'jatuh tempo <b>dalam ' . $remind['diff'] . ' hari</b>!';
                                        }
                                        ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-100 dark:divide-blue-900/40">
                            <thead class="bg-blue-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">#</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">Judul</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">Deskripsi</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">Prioritas</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">Tanggal Dibuat</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">Jatuh Tempo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-blue-400 uppercase dark:text-blue-300">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-blue-50 dark:divide-blue-900/40">
                                <?php
                                function format_tanggal($tanggal)
                                {
                                    $bulan = [
                                        1 => 'Januari',
                                        'Februari',
                                        'Maret',
                                        'April',
                                        'Mei',
                                        'Juni',
                                        'Juli',
                                        'Agustus',
                                        'September',
                                        'Oktober',
                                        'November',
                                        'Desember'
                                    ];
                                    if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $tanggal)) {
                                        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $tanggal);
                                        if ($dt) {
                                            return $dt->format('d') . ' ' . $bulan[(int)$dt->format('m')] . ' ' . $dt->format('Y');
                                        }
                                    } else {
                                        $parts = explode('-', $tanggal);
                                        if (count($parts) == 3) {
                                            return $parts[2] . ' ' . $bulan[(int)$parts[1]] . ' ' . $parts[0];
                                        }
                                    }
                                    return htmlspecialchars($tanggal);
                                }

                                function badge_prioritas($prioritas)
                                {
                                    switch ($prioritas) {
                                        case 'Tinggi':
                                            return '<span class="inline-flex items-center px-3 py-1 rounded-full bg-purple-100 text-purple-700 font-semibold text-xs border border-purple-300 dark:bg-purple-900/40 dark:text-purple-200 dark:border-purple-700">Tinggi</span>';
                                        case 'Sedang':
                                            return '<span class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold text-xs border border-blue-300 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700">Sedang</span>';
                                        case 'Rendah':
                                            return '<span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold text-xs border border-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-700">Rendah</span>';
                                        default:
                                            return '<span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-50 text-gray-400 font-semibold text-xs border border-gray-100 dark:bg-gray-900/40 dark:text-gray-500 dark:border-gray-800">-</span>';
                                    }
                                }

                                function potong_deskripsi($desc, $max = 50)
                                {
                                    $desc = strip_tags($desc);
                                    if (mb_strlen($desc) > $max) {
                                        return htmlspecialchars(mb_substr($desc, 0, $max)) . '...';
                                    }
                                    return htmlspecialchars($desc);
                                }

                                $i = 1;
                                foreach ($filtered_todos as $todo): ?>
                                    <tr class="hover:bg-blue-50 dark:hover:bg-blue-900/40 transition-colors">
                                        <td class="px-4 py-2 dark:text-gray-200"><?= $i++ ?></td>
                                        <td class="px-4 py-2 dark:text-gray-200"><?= htmlspecialchars($todo['title']) ?></td>
                                        <td class="px-4 py-2 max-w-xs truncate dark:text-gray-200" title="<?= htmlspecialchars($todo['description']) ?>">
                                            <?= potong_deskripsi($todo['description'], 50) ?>
                                        </td>
                                        <td class="px-4 py-2">
                                            <?= isset($todo['priority']) ? badge_prioritas($todo['priority']) : badge_prioritas('') ?>
                                        </td>
                                        <td class="px-4 py-2 dark:text-gray-200">
                                            <?= isset($todo['created_at']) ? format_tanggal($todo['created_at']) : '-' ?>
                                        </td>
                                        <td class="px-4 py-2 dark:text-gray-200"><?= format_tanggal($todo['due_date']) ?></td>
                                        <td class="px-4 py-2">
                                            <?php if ($todo['status'] == 'Selesai'): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-semibold border border-green-300 dark:bg-green-900/40 dark:text-green-300 dark:border-green-700">
                                                    Selesai
                                                </span>
                                            <?php elseif ($todo['status'] == 'Proses'): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-yellow-50 text-yellow-700 text-xs font-semibold border border-yellow-200 dark:bg-yellow-900/40 dark:text-yellow-200 dark:border-yellow-600">
                                                    Proses
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-600 text-xs font-semibold border border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700">
                                                    Belum
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2 flex gap-2">
                                            <button onclick="openModal('edit', <?= htmlspecialchars(json_encode($todo)) ?>)" class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded text-xs dark:bg-yellow-600 dark:hover:bg-yellow-700">Edit</button>
                                            <button onclick="openModal('delete', <?= $todo['id'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs dark:bg-red-700 dark:hover:bg-red-800">Hapus</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($filtered_todos)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-gray-400 py-4 dark:text-gray-500">Tidak ada tugas ditemukan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <!-- Modal Tambah, Edit, Hapus -->
    <div id="modal-todo" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative border border-blue-100 dark:bg-gray-900 dark:border-gray-800">
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl dark:text-gray-300 dark:hover:text-gray-100">&times;</button>
            <?= handle_todo_form_submit(); ?>
            <form id="todo-form" method="post" action="">
                <input type="hidden" name="id" id="todo-id">
                <h2 id="modal-title" class="text-xl font-bold mb-4 text-blue-700 dark:text-blue-200">Tambah Todo</h2>
                <div class="mb-3">
                    <label class="block text-gray-700 mb-1 dark:text-gray-200">Judul</label>
                    <input type="text" name="title" id="todo-title" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200" required>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-700 mb-1 dark:text-gray-200">Deskripsi</label>
                    <textarea name="description" id="todo-desc" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-700 mb-1 dark:text-gray-200">Prioritas</label>
                    <select name="priority" id="todo-priority" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200" required>
                        <option value="">Pilih Prioritas</option>
                        <option value="Tinggi">Tinggi</option>
                        <option value="Sedang">Sedang</option>
                        <option value="Rendah">Rendah</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-700 mb-1 dark:text-gray-200">Tanggal Jatuh Tempo</label>
                    <input type="date" name="due_date" id="todo-due" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200" required>
                </div>
                <div class="mb-3" id="status-group" style="display:none;">
                    <label class="block text-gray-700 mb-1 dark:text-gray-200">Status</label>
                    <select name="status" id="todo-status" class="w-full border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
                        <option value="Belum">Belum</option>
                        <option value="Proses">Proses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Batal</button>
                    <button type="submit" id="modal-submit" class="px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-800">Simpan</button>
                </div>
            </form>
            <!-- Hapus Modal -->
            <div id="delete-group" class="hidden">
                <p class="mb-4 text-gray-700 dark:text-gray-200">Yakin ingin menghapus tugas ini?</p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">Batal</button>
                    <?= handle_hapus_todo(); ?>
                    <form id="delete-form" method="post" action="">
                        <input type="hidden" name="delete_id" id="delete-id">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Dark mode toggle logic
        const darkModeToggle = document.getElementById('darkModeToggle');
        const html = document.documentElement;
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
    <script src="assets/js/todo.js"></script>
</body>

</html>