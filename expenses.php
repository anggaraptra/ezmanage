<?php
require_once 'functions/functions.php';
cek_session();

// Handle Export CSV before any output
if (isset($_POST['export_csv'])) {
    $user_login = $_SESSION['user'];
    $user_id = $user_login['id'];
    $csv_expenses = [];
    $res = dbquery("SELECT e.date, c.name as category, e.description, e.amount, e.currency 
        FROM expenses e 
        LEFT JOIN categories_expenses c ON e.category_id = c.id 
        WHERE e.user_id=$user_id 
        ORDER BY e.date DESC");
    while ($row = mysqli_fetch_assoc($res)) {
        $row['currency'] = $row['currency'] ?? 'IDR';
        $csv_expenses[] = $row;
    }
    if (count($csv_expenses) === 0) {
        // Do not process if there is no data
        header('Location: expenses.php');
        exit;
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=pengeluaran_' . date('Ymd') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Tanggal', 'Kategori', 'Deskripsi', 'Jumlah', 'Mata Uang']);
    foreach ($csv_expenses as $exp) {
        fputcsv($output, [
            $exp['date'],
            $exp['category'],
            $exp['description'],
            $exp['amount'],
            $exp['currency']
        ]);
    }
    fclose($output);
    exit;
}

// Define constants for repeated literals
define('REDIRECT_EXPENSES', 'Location: expenses.php');

// Get user login
$user_login = $_SESSION['user'];
$user_id = $user_login['id'];

// Handle add category
if (isset($_POST['add_category']) && !empty(trim($_POST['new_category']))) {
    $new_cat = trim($_POST['new_category']);
    $sql = "INSERT INTO categories_expenses (user_id, name, created_at) VALUES ($user_id, '" . addslashes($new_cat) . "', NOW())";
    dbquery($sql);
    header(REDIRECT_EXPENSES);
    exit;
}

// Handle delete category
if (isset($_GET['delcat'])) {
    $cat_id = intval($_GET['delcat']);
    $sql = "DELETE FROM categories_expenses WHERE id=$cat_id AND user_id=$user_id";
    dbquery($sql);
    header(REDIRECT_EXPENSES);
    exit;
}

// Handle add expense
$add_expense_success = false;
if (isset($_POST['add_expense'])) {
    $amount = floatval($_POST['amount']);
    $date = $_POST['date'];
    $category = intval($_POST['category']);
    $desc = addslashes(trim($_POST['description']));
    $currency = $_POST['currency'];
    if ($amount > 0 && $date && $category && $currency) {
        $sql = "INSERT INTO expenses (user_id, amount, currency, date, category_id, description, created_at) VALUES ($user_id, $amount, '$currency', '$date', $category, '$desc', NOW())";
        dbquery($sql);
        $add_expense_success = true;
        header(REDIRECT_EXPENSES);
        exit;
    }
}

// Handle delete expense
if (isset($_GET['delexp'])) {
    $exp_id = intval($_GET['delexp']);
    $sql = "DELETE FROM expenses WHERE id=$exp_id AND user_id=$user_id";
    dbquery($sql);
    header(REDIRECT_EXPENSES);
    exit;
}

// Handle edit category
if (isset($_POST['edit_category']) && !empty(trim($_POST['edit_category_name'])) && isset($_POST['edit_category_id'])) {
    $edit_id = intval($_POST['edit_category_id']);
    $edit_name = trim($_POST['edit_category_name']);
    $sql = "UPDATE categories_expenses SET name='" . addslashes($edit_name) . "' WHERE id=$edit_id AND user_id=$user_id";
    dbquery($sql);
    header(REDIRECT_EXPENSES);
    exit;
}

// Get categories from database
$categories = [];
$res = dbquery("SELECT id, name FROM categories_expenses WHERE user_id=$user_id ORDER BY name");
while ($row = mysqli_fetch_assoc($res)) {
    $categories[] = ['id' => $row['id'], 'name' => $row['name']];
}

// Get expenses list
$expenses = [];
$res = dbquery("SELECT e.id, e.date, c.name as category, e.description, e.amount, e.category_id FROM expenses e LEFT JOIN categories_expenses c ON e.category_id = c.id WHERE e.user_id=$user_id ORDER BY e.date DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $expenses[] = [
        'id' => $row['id'],
        'date' => $row['date'],
        'category' => $row['category'],
        'description' => $row['description'],
        'amount' => $row['amount'],
        'currency' => 'IDR' // default, because currency field does not exist in expenses table
    ];
}

// Calculate total per currency (only IDR)
$total = [];
foreach ($expenses as $exp) {
    $curr = $exp['currency'];
    if (!isset($total[$curr])) {
        $total[$curr] = 0;
    }
    $total[$curr] += $exp['amount'];
}

// Monthly report (only IDR)
$monthly_report = [];
$res = dbquery("SELECT DATE_FORMAT(date, '%Y-%m') as month, SUM(amount) as total FROM expenses WHERE user_id=$user_id GROUP BY month ORDER BY month DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $monthly_report[$row['month']] = ['IDR' => $row['total']];
}

// List of currencies (only IDR, because currency field does not exist in expenses table)
$currencies = [
    'IDR' => 'Rupiah (IDR)'
];
$selected_currency = $_POST['currency'] ?? 'IDR';

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
    <title>EzManage - Pengeluaran</title>
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
                <a href="dashboard.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-700 dark:text-gray-300 dark:hover:bg-blue-900/40 dark:hover:text-blue-200">
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
                <a href="expenses.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-blue-700 bg-blue-100 font-medium hover:bg-blue-200 dark:text-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60">
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
        <div class="flex-1 flex flex-col md:ml-60 ml-20">
            <!-- Navbar -->
            <header class="bg-white/80 backdrop-blur shadow-sm flex items-center justify-between px-4 md:px-10 py-4 sticky top-0 z-20 dark:bg-gray-900/80 dark:shadow-gray-900/30">
                <h1 class="text-xl md:text-2xl font-bold text-blue-700 dark:text-blue-200">Pengeluaran</h1>
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
            <main class="flex-1 p-2 md:p-6 lg:p-10 max-w-full w-full">
                <div class="mb-6">
                    <h2 class="text-lg md:text-xl font-semibold text-blue-800 mb-1 dark:text-blue-200">
                        Halo, <?= htmlspecialchars(get_user_by_id($user_login['id'])['fullname']) ?>!
                    </h2>
                    <p class="text-gray-500 dark:text-gray-300">
                        Selamat datang di halaman Pengeluaran! Di sini Anda dapat mencatat dan memantau pengeluaran Anda!
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Form Tambah Pengeluaran -->
                    <section class="col-span-1 bg-white rounded-lg shadow p-6 dark:bg-gray-900 dark:border dark:border-gray-800 flex flex-col justify-between h-full">
                        <h3 class="text-lg font-semibold mb-4 text-blue-700 dark:text-blue-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Pengeluaran
                        </h3>
                        <form action="" method="post" class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-gray-600 dark:text-gray-300 mb-1">Jumlah</label>
                                <input
                                    type="text"
                                    name="amount"
                                    id="amountInput"
                                    required
                                    class="border rounded px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100"
                                    placeholder="Masukkan jumlah (contoh: 10000)"
                                    inputmode="decimal"
                                    autocomplete="off">
                            </div>
                            <div>
                                <label class="block text-gray-600 dark:text-gray-300 mb-1">Tanggal</label>
                                <input type="date" name="date" required class="border rounded px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                            </div>
                            <div>
                                <label class="block text-gray-600 dark:text-gray-300 mb-1">Kategori</label>
                                <select name="category" required class="border rounded px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                    <option value="">Pilih Kategori</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 dark:text-gray-300 mb-1">Mata Uang</label>
                                <select name="currency" required class="border rounded px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                    <?php foreach ($currencies as $code => $label): ?>
                                        <option value="<?= $code ?>" <?= $selected_currency === $code ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 dark:text-gray-300 mb-1">Deskripsi</label>
                                <input type="text" name="description" class="border rounded px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Masukkan deskripsi (opsional)">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" name="add_expense" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">Tambah</button>
                            </div>
                        </form>
                        <?php if ($add_expense_success): ?>
                            <div class="mt-3 text-green-600 dark:text-green-400">Pengeluaran berhasil ditambahkan.</div>
                        <?php endif; ?>
                    </section>

                    <!-- Daftar Pengeluaran -->
                    <section class="col-span-2 bg-white rounded-lg shadow p-6 dark:bg-gray-900 dark:border dark:border-gray-800 flex flex-col h-full">
                        <div class="flex flex-col md:flex-row md:items-center justify-between mb-4 gap-2">
                            <h3 class="text-lg font-semibold text-blue-700 dark:text-blue-200 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                Daftar Pengeluaran
                            </h3>
                            <form method="post" action="">
                                <button type="submit" name="export_csv" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-800 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8m0 0l-3-3m3 3l3-3M4 4h16v16H4V4z" />
                                    </svg>
                                    Ekspor CSV
                                </button>
                            </form>
                        </div>
                        <div class="overflow-x-auto">
                            <div class="relative">
                                <table class="min-w-full bg-white border rounded shadow text-sm dark:bg-gray-900 dark:border-gray-800 dark:text-gray-100">
                                    <thead class="sticky top-0 z-10 bg-blue-50 dark:bg-blue-900/30">
                                        <tr>
                                            <th class="py-2 px-3 border-b dark:border-gray-800">Tanggal</th>
                                            <th class="py-2 px-3 border-b dark:border-gray-800">Kategori</th>
                                            <th class="py-2 px-3 border-b dark:border-gray-800">Deskripsi</th>
                                            <th class="py-2 px-3 border-b dark:border-gray-800 text-right">Jumlah</th>
                                            <th class="py-2 px-3 border-b dark:border-gray-800">Mata Uang</th>
                                            <th class="py-2 px-3 border-b dark:border-gray-800">Aksi</th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="overflow-y-auto h-full" style="max-height: 360px;">
                                    <div class="w-full overflow-x-auto">
                                        <table class="min-w-full bg-white text-sm dark:bg-gray-900 dark:text-gray-100">
                                            <tbody>
                                                <?php if (empty($expenses)): ?>
                                                    <tr>
                                                        <td colspan="6" class="py-4 text-center text-gray-400 dark:text-gray-500">Belum ada pengeluaran.</td>
                                                    </tr>
                                                <?php else: ?>
                                                    <?php foreach ($expenses as $exp): ?>
                                                        <tr class="hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                                            <td class="py-2 px-3 border-b dark:border-gray-800 whitespace-nowrap"><?= htmlspecialchars($exp['date']) ?></td>
                                                            <td class="py-2 px-3 border-b dark:border-gray-800 whitespace-nowrap"><?= htmlspecialchars($exp['category']) ?></td>
                                                            <td class="py-2 px-3 border-b dark:border-gray-800 max-w-xs truncate" title="<?= htmlspecialchars($exp['description']) ?>">
                                                                <?php
                                                                $desc = $exp['description'];
                                                                if (mb_strlen($desc) > 40) {
                                                                    echo htmlspecialchars(mb_substr($desc, 0, 40)) . '...';
                                                                } else {
                                                                    echo htmlspecialchars($desc);
                                                                }
                                                                ?>
                                                            </td>
                                                            <td class="py-2 px-3 border-b text-right dark:border-gray-800 whitespace-nowrap"><?= number_format($exp['amount'], 2, ',', '.') ?></td>
                                                            <td class="py-2 px-3 border-b dark:border-gray-800 whitespace-nowrap"><?= htmlspecialchars($exp['currency']) ?></td>
                                                            <td class="py-2 px-3 border-b dark:border-gray-800 whitespace-nowrap">
                                                                <a href="?delexp=<?= $exp['id'] ?>" class="text-red-600 hover:underline dark:text-red-400" onclick="return confirm('Hapus pengeluaran ini?')">Hapus</a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($expenses) && !empty($total)): ?>
                            <div class="mt-4">
                                <table class="min-w-full bg-blue-50 border rounded shadow text-sm dark:bg-blue-900/30 dark:border-gray-800 dark:text-gray-100">
                                    <tbody>
                                        <?php foreach ($total as $curr => $amt): ?>
                                            <tr class="font-semibold">
                                                <td class="py-2 px-3 text-right" colspan="3">Total (<?= htmlspecialchars($curr) ?>)</td>
                                                <td class="py-2 px-3 text-right"><?= number_format($amt, 2, ',', '.') ?></td>
                                                <td class="py-2 px-3"><?= htmlspecialchars($curr) ?></td>
                                                <td></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Manajemen Kategori -->
                    <section class="bg-white rounded-lg shadow p-6 dark:bg-gray-900 dark:border dark:border-gray-800 flex flex-col h-full">
                        <h3 class="text-lg font-semibold mb-3 text-blue-700 dark:text-blue-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Manajemen Kategori
                        </h3>
                        <form action="" method="post" class="flex gap-2 mb-4">
                            <input type="text" name="new_category" placeholder="Kategori baru" class="border rounded px-3 py-2 flex-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                            <button type="submit" name="add_category" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-800">Tambah</button>
                        </form>
                        <?php
                        // Cek jika ada error hapus kategori
                        if (isset($_GET['catdel_error'])) {
                            echo '<div class="mb-3 text-red-600 dark:text-red-400">Kategori tidak bisa dihapus karena masih digunakan pada pengeluaran.</div>';
                        }
                        // Ambil daftar kategori yang sedang digunakan
                        $used_category_ids = [];
                        $res_used = dbquery("SELECT DISTINCT category_id FROM expenses WHERE user_id=$user_id");
                        while ($row = mysqli_fetch_assoc($res_used)) {
                            if ($row['category_id']) $used_category_ids[] = $row['category_id'];
                        }
                        ?>
                        <ul class="divide-y divide-blue-100 dark:divide-gray-800">
                            <?php foreach ($categories as $cat): ?>
                                <li class="py-2 flex items-center justify-between">
                                    <span class="text-gray-700 dark:text-gray-200"><?= htmlspecialchars($cat['name']) ?></span>
                                    <span>
                                        <button type="button"
                                            class="text-blue-600 hover:underline mr-2 dark:text-blue-400 dark:hover:text-blue-300"
                                            onclick="openEditCategoryModal(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')">
                                            Edit
                                        </button>
                                        <?php if (in_array($cat['id'], $used_category_ids)): ?>
                                            <button type="button"
                                                class="text-gray-400 cursor-not-allowed dark:text-gray-600"
                                                title="Kategori sedang digunakan, tidak bisa dihapus"
                                                disabled>
                                                Hapus
                                            </button>
                                        <?php else: ?>
                                            <button type="button"
                                                class="text-red-600 hover:underline dark:text-red-400 dark:hover:text-red-300"
                                                onclick="openDeleteCategoryModal(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>')">
                                                Hapus
                                            </button>
                                        <?php endif; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>

                    <!-- Laporan Pengeluaran -->
                    <section class="col-span-2 bg-white rounded-lg shadow p-6 dark:bg-gray-900 dark:border dark:border-gray-800 flex flex-col h-full">
                        <h3 class="text-lg font-semibold mb-3 text-blue-700 dark:text-blue-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M4 10h16M4 14h16M4 18h16" />
                            </svg>
                            Laporan Pengeluaran Bulanan
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white border rounded shadow text-sm mb-4 dark:bg-gray-900 dark:border-gray-800 dark:text-gray-100">
                                <thead>
                                    <tr class="bg-blue-50 dark:bg-blue-900/30">
                                        <th class="py-2 px-3 border-b dark:border-gray-800">Bulan</th>
                                        <th class="py-2 px-3 border-b dark:border-gray-800">Total Pengeluaran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($monthly_report)): ?>
                                        <tr>
                                            <td colspan="2" class="py-4 text-center text-gray-400 dark:text-gray-500">Belum ada data laporan.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($monthly_report as $month => $amounts): ?>
                                            <tr class="hover:bg-blue-50 dark:hover:bg-blue-900/20">
                                                <td class="py-2 px-3 border-b dark:border-gray-800"><?= htmlspecialchars($month) ?></td>
                                                <td class="py-2 px-3 border-b text-right dark:border-gray-800">
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
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-gray-500 text-sm dark:text-gray-400">* Data laporan di atas real-time.</div>
                    </section>
                </div>

                <!-- Modal Edit Kategori -->
                <div id="editCategoryModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative dark:bg-gray-900 dark:text-gray-100">
                        <button onclick="closeEditCategoryModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl dark:hover:text-gray-200">&times;</button>
                        <h4 class="text-lg font-semibold mb-4 text-blue-700 dark:text-blue-200">Edit Kategori</h4>
                        <form id="editCategoryForm" action="" method="post" class="flex flex-col gap-3">
                            <input type="hidden" name="edit_category_id" id="edit_category_id">
                            <label class="block text-gray-600 dark:text-gray-300 mb-1">Nama Kategori</label>
                            <input type="text" name="edit_category_name" id="edit_category_name" class="border rounded px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" required>
                            <div class="flex gap-2 mt-4">
                                <button type="submit" name="edit_category" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">Simpan</button>
                                <button type="button" onclick="closeEditCategoryModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Delete Kategori -->
                <div id="deleteCategoryModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-96 relative dark:bg-gray-900 dark:text-gray-100">
                        <button onclick="closeDeleteCategoryModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-xl dark:hover:text-gray-200">&times;</button>
                        <h4 class="text-lg font-semibold mb-4 text-red-600 dark:text-red-400">Hapus Kategori</h4>
                        <form id="deleteCategoryForm" action="" method="get" class="flex flex-col gap-3">
                            <input type="hidden" name="delcat" id="delete_category_id">
                            <p>Apakah Anda yakin ingin menghapus kategori <span id="delete_category_name" class="font-semibold"></span>?</p>
                            <div class="flex gap-2 mt-4">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800">Hapus</button>
                                <button type="button" onclick="closeDeleteCategoryModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        // Currency labels from PHP to JS
        const currencyLabels = <?php echo json_encode($currencies); ?>;

        function formatCurrency(value, currency) {
            if (!value) return '';
            let number = value.replace(/[^0-9.,]/g, '').replace(',', '.');
            let floatVal = parseFloat(number);
            if (isNaN(floatVal)) return '';
            if (currency === 'IDR') {
                return 'Rp ' + floatVal.toLocaleString('id-ID', {
                    minimumFractionDigits: 0
                });
            }
            // Add more currency formatting if needed
            return floatVal.toLocaleString(undefined, {
                minimumFractionDigits: 2
            });
        }

        const amountInput = document.getElementById('amountInput');
        const currencySelect = document.querySelector('select[name="currency"]');

        function getCurrency() {
            return currencySelect ? currencySelect.value : 'IDR';
        }

        let lastRawValue = '';

        // Set placeholder based on currency
        function updatePlaceholder() {
            let curr = getCurrency();
            let label = currencyLabels[curr] || curr;
            if (curr === 'IDR') {
                amountInput.placeholder = 'Masukkan jumlah (contoh: 10000)';
            } else {
                amountInput.placeholder = 'Masukkan jumlah (' + label + ')';
            }
        }
        updatePlaceholder();

        amountInput.addEventListener('input', function(e) {
            // Remove all non-numeric characters except comma and dot
            let raw = amountInput.value.replace(/[^0-9]/g, '');
            lastRawValue = raw;
            let currency = getCurrency();
            amountInput.value = formatCurrency(raw, currency);
        });

        // On form submit, convert formatted value to plain number
        amountInput.form && amountInput.form.addEventListener('submit', function(e) {
            let raw = amountInput.value.replace(/[^0-9]/g, '');
            amountInput.value = raw;
        });

        // Update formatting and placeholder if currency changes
        if (currencySelect) {
            currencySelect.addEventListener('change', function() {
                updatePlaceholder();
                let raw = lastRawValue;
                amountInput.value = formatCurrency(raw, getCurrency());
            });
        }

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
    <script src="assets/js/expenses.js"></script>
</body>

</html>