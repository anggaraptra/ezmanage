<?php
require_once 'functions/functions.php';
cek_session();

$user_login = $_SESSION['user'];

// Ambil data user
$user = get_user_by_id($user_login['id']);
// Cek apakah user sudah upload foto profile
$profilePic = !empty($user['profile_pic']) && file_exists('assets/profiles/' . $user['profile_pic'])
    ? 'assets/profiles/' . $user['profile_pic']
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['fullname']) . '&background=4f8ef7&color=fff';

// Fungsi hapus semua history kalkulasi user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_history'])) {
    $user_id = intval($user_login['id']);
    dbquery("DELETE FROM calculations WHERE user_id = $user_id");
    // Setelah hapus, reload halaman agar riwayat kosong
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

// Tangani request AJAX untuk menyimpan kalkulasi (POST dari JS)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['expression']) &&
    array_key_exists('result', $_POST) // result bisa string kosong
) {
    require_once __DIR__ . '/functions/functions.php';
    cek_session();
    header('Content-Type: application/json');
    $user_id = intval($_SESSION['user']['id']);
    $expression = trim($_POST['expression']);
    $result = trim($_POST['result']);
    // Hanya izinkan karakter yang aman pada ekspresi
    if (preg_match('/[^0-9\+\-\*\/\(\)\.\,\s^%a-zA-Z]/', $expression)) {
        echo json_encode(['status' => 'error', 'message' => 'Ekspresi tidak valid']);
        exit;
    }
    // Escape untuk SQL
    $expression_esc = mysqli_real_escape_string($mysqli, $expression);
    $result_esc = mysqli_real_escape_string($mysqli, $result);
    $sql = "INSERT INTO calculations (user_id, calculation, result, created_at) VALUES ($user_id, '$expression_esc', '$result_esc', NOW())";
    dbquery($sql);
    echo json_encode(['status' => 'ok']);
    exit;
}

/*
 * Tangani submit form dan redirect SEBELUM output apapun
 * Exception khusus untuk ekspresi matematika tidak valid
 */
class InvalidMathExpressionException extends Exception {}

// Cegah duplikasi insert saat refresh menggunakan POST-REDIRECT-GET
$calc_error = '';
$calc_result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expression']) && !isset($_POST['result'])) {
    // Hanya proses POST dari form HTML, bukan dari AJAX
    $expression = trim($_POST['expression']);
    $user_id = $user_login['id'];
    $result = null;

    // Fungsi untuk evaluasi ekspresi matematika sederhana
    function evalMathExpression($expr)
    {
        // Ganti fungsi matematika ke versi PHP
        $expr = str_ireplace(
            ['sin', 'cos', 'tan', 'log', 'sqrt'],
            ['sin', 'cos', 'tan', 'log10', 'sqrt'],
            $expr
        );
        // Ganti persen (misal 10% jadi 0.1)
        $expr = preg_replace('/(\d+(\.\d+)?)\s*%/', '($1/100)', $expr);
        // Ganti koma dengan titik
        $expr = str_replace(',', '.', $expr);
        // Cek karakter tidak valid
        if (preg_match('/[^0-9\+\-\*\/\(\)\.\,\s^%a-zA-Z]/', $expr)) {
            throw new InvalidMathExpressionException('Ekspresi mengandung karakter tidak valid.');
        }
        // Ganti ^ dengan pow() untuk SEMUA kasus (termasuk variabel/ekspresi)
        $expr = preg_replace('/([a-zA-Z0-9_.()]+)\s*\^\s*([a-zA-Z0-9_.()]+)/', 'pow($1,$2)', $expr);
        // Ganti % antara dua ekspresi menjadi operator modulo PHP
        $expr = preg_replace('/([a-zA-Z0-9_.()]+)\s*%\s*([a-zA-Z0-9_.()]+)/', '($1%$2)', $expr);
        // Evaluasi dengan eval
        $expr = '$res = ' . $expr . ';';
        $res = null;
        try {
            eval($expr);
        } catch (Throwable $e) {
            throw new InvalidMathExpressionException('Ekspresi tidak valid.');
        }
        if (!is_numeric($res)) {
            throw new InvalidMathExpressionException('Ekspresi tidak valid.');
        }
        return $res;
    }

    try {
        // Evaluasi ekspresi matematika
        $result = evalMathExpression($expression);
        // Pembulatan hasil ke 4 desimal, hapus nol di belakang
        $result_rounded = rtrim(rtrim(number_format((float)$result, 4, '.', ''), '0'), '.');
        $calc_result = $result_rounded;
        // Simpan ke database dengan query parameterized
        $user_id_esc = intval($user_id);
        $expression_esc = mysqli_real_escape_string($mysqli, $expression);
        $result_esc = mysqli_real_escape_string($mysqli, $result_rounded);
        dbquery("INSERT INTO calculations (user_id, calculation, result, created_at) VALUES ($user_id_esc, '$expression_esc', '$result_esc', NOW())");

        // Redirect untuk mencegah resubmission saat refresh (PRG pattern)
        header("Location: " . $_SERVER['REQUEST_URI'] . "?calc_result=" . urlencode($calc_result));
        exit;
    } catch (InvalidMathExpressionException $e) {
        $calc_error = $e->getMessage();
    } catch (Exception $e) {
        $calc_error = 'Terjadi kesalahan.';
    }
} elseif (isset($_GET['calc_result'])) {
    // Ambil hasil kalkulasi dari parameter GET jika ada
    $calc_result = $_GET['calc_result'];
}

// Ambil riwayat kalkulasi user
$user_id = $user_login['id'];
$history = [];
$user_id_safe = intval($user_id);
$q = dbquery("SELECT calculation, result, created_at FROM calculations WHERE user_id = $user_id_safe ORDER BY created_at DESC LIMIT 50");
if ($q) {
    while ($row = mysqli_fetch_assoc($q)) {
        $history[] = [
            'calculation' => $row['calculation'],
            'result' => $row['result'],
            'created_at' => $row['created_at']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='blue' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M9 17v-2a4 4 0 014-4h3m4 0a9 9 0 11-18 0 9 9 0 0118 0z'/%3E%3C/svg%3E">
    <title>EzManage - Kalkulator</title>
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
                <a href="calculations.php" class="flex items-center gap-2 px-3 py-2 rounded-lg text-blue-700 bg-blue-100 font-medium hover:bg-blue-200 dark:text-blue-200 dark:bg-blue-900/40 dark:hover:bg-blue-900/60">
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
                <h1 class="text-xl md:text-2xl font-bold text-blue-700 dark:text-blue-200">Kalkulator</h1>
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
                    <h2 class="text-lg md:text-xl font-semibold text-blue-800 mb-1 dark:text-blue-200">
                        Halo, <?= htmlspecialchars(get_user_by_id($user_login['id'])['fullname']) ?>!
                    </h2>
                    <p class="text-gray-500 dark:text-gray-300">
                        Selamat datang di Kalkulator Online. Hitung ekspresi matematika & keuangan, serta konversi satuan dengan mudah!
                    </p>
                </div>

                <!-- Kalkulator Matematika & Riwayat -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
                    <!-- Kalkulator -->
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 flex flex-col justify-between min-h-[420px]">
                        <h2 class="text-xl font-bold text-blue-600 dark:text-blue-300 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v2M8 2v2M3 6h18M4 10h16M4 14h16M4 18h16" />
                                <rect x="8" y="14" width="8" height="6" rx="2" />
                            </svg>
                            Kalkulator Matematika
                        </h2>
                        <div class="flex-1 flex flex-col justify-between">
                            <!-- Error -->
                            <div id="calc-error" class="bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded mb-3 text-center dark:bg-red-900 dark:border-red-700 dark:text-red-300" style="display:none;"></div>
                            <!-- Form -->
                            <form id="math-calc-form" class="flex flex-col gap-3 mb-3" autocomplete="off">
                                <input type="text" name="expression" id="expression" placeholder="Contoh: 5 + sin(0.5) * 10" required class="border rounded-lg px-3 py-2 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" />
                                <div class="flex flex-wrap gap-2">
                                    <?php
                                    $ops = ['+', '-', '*', '/', '(', ')', '^', '%', 'sin()', 'cos()', 'tan()', 'log()', 'sqrt()'];
                                    foreach ($ops as $op): ?>
                                        <button type="button" onclick="insertOp('<?= str_replace('()', '', $op) ?>')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg hover:bg-blue-200 text-sm dark:bg-blue-900/40 dark:text-blue-200 dark:hover:bg-blue-900/60"><?= $op ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 w-full dark:bg-blue-700 dark:hover:bg-blue-800">Hitung</button>
                                    <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 w-full dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" onclick="resetCalcResult()">Reset</button>
                                </div>
                            </form>
                            <!-- Hasil -->
                            <div id="calc-result" class="bg-blue-100 border border-blue-300 text-blue-800 font-bold px-4 py-3 rounded-lg mb-3 text-center dark:bg-blue-900/40 dark:border-blue-700 dark:text-blue-200" style="display:none;">
                                Hasil: <span id="calc-result-value"></span>
                            </div>
                            <div class="text-xs text-gray-500 mt-2 dark:text-gray-400">
                                <b>Fungsi:</b> sin, cos, tan, log, sqrt, %, ^<br>
                                <b>Contoh:</b> <span class="font-mono">sin(0.5) + 10%</span>
                            </div>
                        </div>
                    </div>
                    <!-- Riwayat Kalkulasi -->
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 flex flex-col min-h-[420px] col-span-1 lg:col-span-2">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-300">Riwayat Kalkulasi</h3>
                            <div class="flex gap-2 flex-wrap">
                                <form method="post" style="display:inline;">
                                    <button type="submit" name="refresh_history" class="bg-blue-100 text-blue-700 px-3 py-1 rounded-lg hover:bg-blue-200 text-sm flex items-center gap-1 dark:bg-blue-900/40 dark:text-blue-200 dark:hover:bg-blue-900/60" title="Muat Ulang">
                                        Muat Ulang
                                    </button>
                                </form>
                                <button type="button" onclick="exportHistoryCSV()" class="bg-green-100 text-green-700 px-3 py-1 rounded-lg hover:bg-green-200 text-sm flex items-center gap-1 dark:bg-green-900/40 dark:text-green-200 dark:hover:bg-green-900/60" title="Ekspor CSV">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    CSV
                                </button>
                                <button type="button" onclick="exportHistoryPDF()" class="bg-red-100 text-red-700 px-3 py-1 rounded-lg hover:bg-red-200 text-sm flex items-center gap-1 dark:bg-red-900/40 dark:text-red-200 dark:hover:bg-red-900/60" title="Ekspor PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        <rect x="6" y="6" width="12" height="12" rx="2" />
                                    </svg>
                                    PDF
                                </button>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua riwayat kalkulasi?');">
                                    <button type="submit" name="delete_history" class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 text-sm flex items-center gap-1 dark:bg-red-700 dark:hover:bg-red-800" title="Hapus Semua Riwayat">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto border border-blue-100 rounded-lg bg-blue-50 p-3 flex-1 dark:bg-blue-900/20 dark:border-blue-900">
                            <?php if (empty($history)): ?>
                                <div class="text-gray-500 text-center py-8 dark:text-gray-400">Belum ada riwayat kalkulasi.</div>
                            <?php else: ?>
                                <table class="w-full text-sm" id="calc-history-table">
                                    <thead>
                                        <tr class="bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                                            <th class="text-left py-2 px-3 rounded-tl-lg">Ekspresi</th>
                                            <th class="text-left py-2 px-3">Hasil</th>
                                            <th class="text-left py-2 px-3 rounded-tr-lg">Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $row): ?>
                                            <?php
                                            $result = $row['result'];
                                            if (is_numeric($result) && floor($result) == $result) {
                                                $display_result = number_format($result, 0, '.', '');
                                            } else {
                                                $display_result = $result;
                                            }
                                            ?>
                                            <tr class="hover:bg-blue-200/60 dark:hover:bg-blue-900/60 cursor-pointer"
                                                onclick="showHistoryCalc('<?= htmlspecialchars($row['calculation'], ENT_QUOTES) ?>', '<?= htmlspecialchars($display_result, ENT_QUOTES) ?>')">
                                                <td class="py-2 px-3 font-mono text-blue-900 dark:text-blue-200"><?= htmlspecialchars($row['calculation']) ?></td>
                                                <td class="py-2 px-3 text-blue-800 font-semibold dark:text-blue-100"><?= htmlspecialchars($display_result) ?></td>
                                                <td class="py-2 px-3 text-gray-500 whitespace-nowrap dark:text-gray-400"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Kalkulator Keuangan -->
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 mt-10">
                    <h2 class="text-xl font-bold text-purple-600 dark:text-purple-300 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                        </svg>
                        Kalkulator Keuangan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Bunga Pinjaman -->
                        <div class="border rounded-xl p-4 flex flex-col dark:border-gray-700 bg-purple-50/30 dark:bg-purple-900/10">
                            <h3 class="font-semibold text-purple-700 dark:text-purple-300 mb-2">Bunga Pinjaman</h3>
                            <form id="loan-interest-form" class="flex flex-col gap-2" onsubmit="event.preventDefault(); calcLoanInterest();">
                                <input type="number" step="any" id="loan-amount" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Jumlah Pinjaman (Rp)" required>
                                <input type="number" step="any" id="loan-rate" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Bunga per Tahun (%)" required>
                                <input type="number" step="any" id="loan-years" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Lama (tahun)" required>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-purple-500 text-white px-3 py-2 rounded-lg hover:bg-purple-600 flex-1 dark:bg-purple-700 dark:hover:bg-purple-800">Hitung</button>
                                    <button type="button" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 flex-1 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" onclick="resetLoanInterest()">Reset</button>
                                </div>
                            </form>
                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                                <span class="font-semibold">Total Bunga:</span>
                                <span id="loan-interest-result" class="text-purple-700 font-bold dark:text-purple-300"></span>
                            </div>
                        </div>
                        <!-- Amortisasi Pinjaman -->
                        <div class="border rounded-xl p-4 flex flex-col dark:border-gray-700 bg-purple-50/30 dark:bg-purple-900/10">
                            <h3 class="font-semibold text-purple-700 dark:text-purple-300 mb-2">Amortisasi Pinjaman</h3>
                            <form id="amortization-form" class="flex flex-col gap-2" onsubmit="event.preventDefault(); calcAmortization();">
                                <input type="number" step="any" id="amort-loan" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Jumlah Pinjaman (Rp)" required>
                                <input type="number" step="any" id="amort-rate" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Bunga per Tahun (%)" required>
                                <input type="number" step="any" id="amort-years" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Lama (tahun)" required>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-purple-500 text-white px-3 py-2 rounded-lg hover:bg-purple-600 flex-1 dark:bg-purple-700 dark:hover:bg-purple-800">Hitung</button>
                                    <button type="button" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 flex-1 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" onclick="resetAmortization()">Reset</button>
                                </div>
                            </form>
                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                                <span class="font-semibold">Angsuran per Bulan:</span>
                                <span id="amortization-result" class="text-purple-700 font-bold dark:text-purple-300"></span>
                            </div>
                        </div>
                        <!-- Kalkulator Investasi -->
                        <div class="border rounded-xl p-4 flex flex-col dark:border-gray-700 bg-purple-50/30 dark:bg-purple-900/10">
                            <h3 class="font-semibold text-purple-700 dark:text-purple-300 mb-2">Kalkulator Investasi</h3>
                            <form id="investment-form" class="flex flex-col gap-2" onsubmit="event.preventDefault(); calcInvestment();">
                                <input type="number" step="any" id="invest-principal" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Modal Awal (Rp)" required>
                                <input type="number" step="any" id="invest-rate" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Bunga per Tahun (%)" required>
                                <input type="number" step="any" id="invest-years" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Lama (tahun)" required>
                                <input type="number" step="any" id="invest-additional" class="border rounded-lg px-3 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Investasi per Tahun (Rp)" value="0">
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-purple-500 text-white px-3 py-2 rounded-lg hover:bg-purple-600 flex-1 dark:bg-purple-700 dark:hover:bg-purple-800">Hitung</button>
                                    <button type="button" class="bg-gray-200 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-300 flex-1 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" onclick="resetInvestment()">Reset</button>
                                </div>
                            </form>
                            <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                                <span class="font-semibold">Nilai Akhir:</span>
                                <span id="investment-result" class="text-purple-700 font-bold dark:text-purple-300"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mt-4 dark:text-gray-400">
                        <b>Keterangan:</b> Semua hasil estimasi, tidak termasuk pajak/biaya lain.<br>
                        <b>Amortisasi:</b> Menggunakan metode angsuran tetap (annuitas).
                    </div>
                </div>

                <!-- Kalkulator Konversi Unit -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                    <!-- Konversi Panjang -->
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-blue-600 dark:text-blue-300 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 17v-2a4 4 0 014-4h8a4 4 0 014 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Konversi Panjang
                        </h2>
                        <form id="length-converter" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertLength();">
                            <div class="flex flex-col gap-2">
                                <input type="number" step="any" id="length-value" class="border rounded-lg px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Nilai" />
                                <div class="flex gap-2">
                                    <select id="length-from" class="border rounded-lg px-2 py-2 w-1/2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                        <option value="m">Meter</option>
                                        <option value="cm">Centimeter</option>
                                        <option value="km">Kilometer</option>
                                        <option value="mm">Milimeter</option>
                                        <option value="in">Inci</option>
                                        <option value="ft">Kaki</option>
                                        <option value="yd">Yard</option>
                                        <option value="mi">Mil</option>
                                    </select>
                                    <span class="flex items-center px-2 dark:text-gray-200">→</span>
                                    <select id="length-to" class="border rounded-lg px-2 py-2 w-1/2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                        <option value="m">Meter</option>
                                        <option value="cm">Centimeter</option>
                                        <option value="km">Kilometer</option>
                                        <option value="mm">Milimeter</option>
                                        <option value="in">Inci</option>
                                        <option value="ft">Kaki</option>
                                        <option value="yd">Yard</option>
                                        <option value="mi">Mil</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 w-full dark:bg-blue-700 dark:hover:bg-blue-800">Konversi</button>
                                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 w-full dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" onclick="resetLengthConverter()">Reset</button>
                            </div>
                            <div class="flex gap-2 items-center mt-2">
                                <span class="font-semibold text-blue-700 dark:text-blue-200">Hasil:</span>
                                <span id="length-result" class="text-blue-700 font-bold dark:text-blue-300"></span>
                            </div>
                        </form>
                    </div>
                    <!-- Konversi Berat -->
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-green-600 dark:text-green-300 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                            </svg>
                            Konversi Berat
                        </h2>
                        <form id="weight-converter" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertWeight();">
                            <div class="flex flex-col gap-2">
                                <input type="number" step="any" id="weight-value" class="border rounded-lg px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Nilai" />
                                <div class="flex gap-2">
                                    <select id="weight-from" class="border rounded-lg px-2 py-2 w-1/2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                        <option value="g">Gram</option>
                                        <option value="kg">Kilogram</option>
                                        <option value="mg">Miligram</option>
                                        <option value="lb">Pound</option>
                                        <option value="oz">Ons</option>
                                        <option value="ton">Ton</option>
                                    </select>
                                    <span class="flex items-center px-2 dark:text-gray-200">→</span>
                                    <select id="weight-to" class="border rounded-lg px-2 py-2 w-1/2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                        <option value="g">Gram</option>
                                        <option value="kg">Kilogram</option>
                                        <option value="mg">Miligram</option>
                                        <option value="lb">Pound</option>
                                        <option value="oz">Ons</option>
                                        <option value="ton">Ton</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 w-full dark:bg-green-700 dark:hover:bg-green-800">Konversi</button>
                                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 w-full dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" onclick="resetWeightConverter()">Reset</button>
                            </div>
                            <div class="flex gap-2 items-center mt-2">
                                <span class="font-semibold text-green-700 dark:text-green-200">Hasil:</span>
                                <span id="weight-result" class="text-green-700 font-bold dark:text-green-300"></span>
                            </div>
                        </form>
                    </div>
                    <!-- Konversi Suhu -->
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-red-600 dark:text-red-300 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8 4 4 0 000-8z" />
                            </svg>
                            Konversi Suhu
                        </h2>
                        <form id="temp-converter" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertTemp();">
                            <div class="flex flex-col gap-2">
                                <input type="number" step="any" id="temp-value" class="border rounded-lg px-3 py-2 w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100" placeholder="Nilai" />
                                <div class="flex gap-2">
                                    <select id="temp-from" class="border rounded-lg px-2 py-2 w-1/2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                        <option value="c">Celsius</option>
                                        <option value="f">Fahrenheit</option>
                                        <option value="k">Kelvin</option>
                                    </select>
                                    <span class="flex items-center px-2 dark:text-gray-200">→</span>
                                    <select id="temp-to" class="border rounded-lg px-2 py-2 w-1/2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100">
                                        <option value="c">Celsius</option>
                                        <option value="f">Fahrenheit</option>
                                        <option value="k">Kelvin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 w-full dark:bg-red-700 dark:hover:bg-red-800">Konversi</button>
                                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 w-full dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700" onclick="resetTempConverter()">Reset</button>
                            </div>
                            <div class="flex gap-2 items-center mt-2">
                                <span class="font-semibold text-red-700 dark:text-red-200">Hasil:</span>
                                <span id="temp-result" class="text-red-700 font-bold dark:text-red-300"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="assets/js/calculations.js"></script>
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

        // Saat halaman dimuat, jika ada hasil (dari PHP PRG), muat ulang riwayat agar selalu terbaru
        <?php if (!empty($calc_result)): ?>
            document.addEventListener('DOMContentLoaded', function() {
                reloadHistory();
                document.getElementById('calc-result-value').textContent = "<?= htmlspecialchars($calc_result) ?>";
                document.getElementById('calc-result').style.display = '';
            });
        <?php endif; ?>
    </script>
</body>

</html>