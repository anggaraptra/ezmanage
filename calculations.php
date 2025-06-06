<?php
require_once 'functions/functions.php';
cek_session();

$user_login = $_SESSION['user'];

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
    <title>EzManage - Kalkulator</title>
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
                <a href="expenses.php" class="flex items-center gap-2 px-3 py-2 rounded hover:bg-white hover:bg-opacity-10 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                    </svg>
                    Pengeluaran
                </a>
                <a href="calculations.php" class="flex items-center gap-2 px-3 py-2 rounded bg-white bg-opacity-10 hover:bg-opacity-20 transition">
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
                <h1 class="text-2xl font-bold text-blue-600">Kalkulator</h1>
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
                    <p class="text-gray-500">Selamat datang di Kalkulator Online. Hitung ekspresi matematika dengan mudah dan lihat riwayat perhitunganmu!</p>
                </div>
                <?php
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
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Kalkulator -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold text-blue-600 mb-4 flex items-center gap-2">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 2v2M8 2v2M3 6h18M4 10h16M4 14h16M4 18h16" />
                                <rect x="8" y="14" width="8" height="6" rx="2" />
                            </svg>
                            Kalkulator
                        </h2>
                        <!-- Tampilkan error jika ada -->
                        <div id="calc-error" class="bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded mb-4 text-center" style="display:none;"></div>
                        <!-- Form kalkulator matematika -->
                        <form id="math-calc-form" class="flex flex-col gap-3 mb-4" autocomplete="off">
                            <input type="text" name="expression" id="expression" placeholder="Contoh: 5 + sin(0.5) * 10" required class="border rounded px-3 py-2 text-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
                            <div class="flex flex-wrap gap-2">
                                <?php
                                // Tombol operator dan fungsi matematika
                                $ops = ['+', '-', '*', '/', '(', ')', '^', '%', 'sin()', 'cos()', 'tan()', 'log()', 'sqrt()'];
                                foreach ($ops as $op): ?>
                                    <button type="button" onclick="insertOp('<?= str_replace('()', '', $op) ?>')" class="bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200 transition text-sm"><?= $op ?></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition w-full">Hitung</button>
                                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition w-full" onclick="resetCalcResult()">Reset</button>
                            </div>
                        </form>
                        <!-- Hasil kalkulasi -->
                        <div id="calc-result" class="bg-blue-100 border border-blue-300 text-blue-800 font-bold px-4 py-3 rounded mb-4 text-center" style="display:none;">
                            Hasil: <span id="calc-result-value"></span>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                            <b>Fungsi yang didukung:</b> sin, cos, tan, log, sqrt, %, ^<br>
                            <b>Contoh:</b> <span class="font-mono">sin(0.5) + 10%</span>
                        </div>
                    </div>

                    <!-- Riwayat Kalkulasi -->
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col h-full">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-blue-600">Riwayat Kalkulasi Anda</h3>
                            <div class="flex gap-2">
                                <!-- Tombol refresh riwayat -->
                                <form method="post" style="display:inline;">
                                    <button type="submit" name="refresh_history" class="bg-blue-100 text-blue-700 px-3 py-1 rounded hover:bg-blue-200 transition text-sm flex items-center gap-1" title="Muat Ulang">
                                        Muat Ulang
                                    </button>
                                </form>
                                <!-- Tombol ekspor CSV -->
                                <button type="button" onclick="exportHistoryCSV()" class="bg-green-100 text-green-700 px-3 py-1 rounded hover:bg-green-200 transition text-sm flex items-center gap-1" title="Ekspor CSV">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    CSV
                                </button>
                                <!-- Tombol ekspor PDF -->
                                <button type="button" onclick="exportHistoryPDF()" class="bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition text-sm flex items-center gap-1" title="Ekspor PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                        <rect x="6" y="6" width="12" height="12" rx="2" />
                                    </svg>
                                    PDF
                                </button>
                            </div>
                        </div>
                        <!-- Tabel riwayat kalkulasi -->
                        <div class="max-h-96 overflow-y-auto border border-blue-100 rounded bg-blue-50 p-3 flex-1" id="calc-history">
                            <?php if (empty($history)): ?>
                                <div class="text-gray-500 text-center py-8">Belum ada riwayat kalkulasi.</div>
                            <?php else: ?>
                                <table class="w-full text-sm" id="calc-history-table">
                                    <thead>
                                        <tr class="bg-blue-100 text-blue-700">
                                            <th class="text-left py-2 px-3 rounded-tl-lg">Ekspresi</th>
                                            <th class="text-left py-2 px-3">Hasil</th>
                                            <th class="text-left py-2 px-3 rounded-tr-lg">Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $row): ?>
                                            <tr class="hover:bg-blue-200/60 transition">
                                                <td class="py-2 px-3 font-mono text-blue-900"><?= htmlspecialchars($row['calculation']) ?></td>
                                                <?php
                                                $result = $row['result'];
                                                if (is_numeric($result) && floor($result) == $result) {
                                                    $display_result = number_format($result, 0, '.', '');
                                                } else {
                                                    $display_result = $result;
                                                }
                                                ?>
                                                <td class="py-2 px-3 text-blue-800 font-semibold"><?= htmlspecialchars($display_result) ?></td>
                                                <td class="py-2 px-3 text-gray-500 whitespace-nowrap"><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Kalkulator Keuangan -->
                <div class="bg-white rounded-lg shadow p-6 mt-10">
                    <h2 class="text-xl font-bold text-purple-600 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                        </svg>
                        Kalkulator Keuangan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Bunga Pinjaman -->
                        <div class="border rounded-lg p-4 flex flex-col">
                            <h3 class="font-semibold text-purple-700 mb-2">Bunga Pinjaman</h3>
                            <form id="loan-interest-form" class="flex flex-col gap-2" onsubmit="event.preventDefault(); calcLoanInterest();">
                                <input type="number" step="any" id="loan-amount" class="border rounded px-3 py-2" placeholder="Jumlah Pinjaman (Rp)" required>
                                <input type="number" step="any" id="loan-rate" class="border rounded px-3 py-2" placeholder="Bunga per Tahun (%)" required>
                                <input type="number" step="any" id="loan-years" class="border rounded px-3 py-2" placeholder="Lama (tahun)" required>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-purple-500 text-white px-3 py-2 rounded hover:bg-purple-600 transition flex-1">Hitung</button>
                                    <button type="button" class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300 transition flex-1" onclick="resetLoanInterest()">Reset</button>
                                </div>
                            </form>
                            <div class="mt-2 text-sm text-gray-700">
                                <span class="font-semibold">Total Bunga:</span>
                                <span id="loan-interest-result" class="text-purple-700 font-bold"></span>
                            </div>
                        </div>
                        <!-- Amortisasi Pinjaman -->
                        <div class="border rounded-lg p-4 flex flex-col">
                            <h3 class="font-semibold text-purple-700 mb-2">Amortisasi Pinjaman</h3>
                            <form id="amortization-form" class="flex flex-col gap-2" onsubmit="event.preventDefault(); calcAmortization();">
                                <input type="number" step="any" id="amort-loan" class="border rounded px-3 py-2" placeholder="Jumlah Pinjaman (Rp)" required>
                                <input type="number" step="any" id="amort-rate" class="border rounded px-3 py-2" placeholder="Bunga per Tahun (%)" required>
                                <input type="number" step="any" id="amort-years" class="border rounded px-3 py-2" placeholder="Lama (tahun)" required>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-purple-500 text-white px-3 py-2 rounded hover:bg-purple-600 transition flex-1">Hitung</button>
                                    <button type="button" class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300 transition flex-1" onclick="resetAmortization()">Reset</button>
                                </div>
                            </form>
                            <div class="mt-2 text-sm text-gray-700">
                                <span class="font-semibold">Angsuran per Bulan:</span>
                                <span id="amortization-result" class="text-purple-700 font-bold"></span>
                            </div>
                        </div>
                        <!-- Kalkulator Investasi -->
                        <div class="border rounded-lg p-4 flex flex-col">
                            <h3 class="font-semibold text-purple-700 mb-2">Kalkulator Investasi</h3>
                            <form id="investment-form" class="flex flex-col gap-2" onsubmit="event.preventDefault(); calcInvestment();">
                                <input type="number" step="any" id="invest-principal" class="border rounded px-3 py-2" placeholder="Modal Awal (Rp)" required>
                                <input type="number" step="any" id="invest-rate" class="border rounded px-3 py-2" placeholder="Bunga per Tahun (%)" required>
                                <input type="number" step="any" id="invest-years" class="border rounded px-3 py-2" placeholder="Lama (tahun)" required>
                                <input type="number" step="any" id="invest-additional" class="border rounded px-3 py-2" placeholder="Investasi per Tahun (Rp)" value="0">
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-purple-500 text-white px-3 py-2 rounded hover:bg-purple-600 transition flex-1">Hitung</button>
                                    <button type="button" class="bg-gray-200 text-gray-700 px-3 py-2 rounded hover:bg-gray-300 transition flex-1" onclick="resetInvestment()">Reset</button>
                                </div>
                            </form>
                            <div class="mt-2 text-sm text-gray-700">
                                <span class="font-semibold">Nilai Akhir:</span>
                                <span id="investment-result" class="text-purple-700 font-bold"></span>
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 mt-4">
                        <b>Keterangan:</b> Semua hasil estimasi, tidak termasuk pajak/biaya lain.<br>
                        <b>Amortisasi:</b> Menggunakan metode angsuran tetap (annuitas).
                    </div>
                </div>

                <!-- Kalkulator Konversi Unit -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-10">
                    <!-- Konversi Panjang -->
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-blue-600 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 17v-2a4 4 0 014-4h8a4 4 0 014 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Konversi Panjang
                        </h2>
                        <form id="length-converter" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertLength();">
                            <div class="flex flex-col gap-2">
                                <input type="number" step="any" id="length-value" class="border rounded px-3 py-2 w-full" placeholder="Nilai" />
                                <div class="flex gap-2">
                                    <select id="length-from" class="border rounded px-2 py-2 w-1/2">
                                        <option value="m">Meter</option>
                                        <option value="cm">Centimeter</option>
                                        <option value="km">Kilometer</option>
                                        <option value="mm">Milimeter</option>
                                        <option value="in">Inci</option>
                                        <option value="ft">Kaki</option>
                                        <option value="yd">Yard</option>
                                        <option value="mi">Mil</option>
                                    </select>
                                    <span class="flex items-center px-2">→</span>
                                    <select id="length-to" class="border rounded px-2 py-2 w-1/2">
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
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition w-full">Konversi</button>
                                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition w-full" onclick="resetLengthConverter()">Reset</button>
                            </div>
                            <div class="flex gap-2 items-center mt-2">
                                <span class="font-semibold">Hasil:</span>
                                <span id="length-result" class="text-blue-700 font-bold"></span>
                            </div>
                        </form>
                    </div>

                    <!-- Konversi Berat -->
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-green-600 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0 8c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17l2.5 2.5" />
                            </svg>
                            Konversi Berat
                        </h2>
                        <form id="weight-converter" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertWeight();">
                            <div class="flex flex-col gap-2">
                                <input type="number" step="any" id="weight-value" class="border rounded px-3 py-2 w-full" placeholder="Nilai" />
                                <div class="flex gap-2">
                                    <select id="weight-from" class="border rounded px-2 py-2 w-1/2">
                                        <option value="g">Gram</option>
                                        <option value="kg">Kilogram</option>
                                        <option value="mg">Miligram</option>
                                        <option value="lb">Pound</option>
                                        <option value="oz">Ons</option>
                                        <option value="ton">Ton</option>
                                    </select>
                                    <span class="flex items-center px-2">→</span>
                                    <select id="weight-to" class="border rounded px-2 py-2 w-1/2">
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
                                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition w-full">Konversi</button>
                                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition w-full" onclick="resetWeightConverter()">Reset</button>
                            </div>
                            <div class="flex gap-2 items-center mt-2">
                                <span class="font-semibold">Hasil:</span>
                                <span id="weight-result" class="text-green-700 font-bold"></span>
                            </div>
                        </form>
                    </div>

                    <!-- Konversi Suhu -->
                    <div class="bg-white rounded-lg shadow p-6 flex flex-col">
                        <h2 class="text-lg font-bold text-red-600 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v10m0 0a4 4 0 100 8 4 4 0 000-8z" />
                            </svg>
                            Konversi Suhu
                        </h2>
                        <form id="temp-converter" class="flex flex-col gap-4" onsubmit="event.preventDefault(); convertTemp();">
                            <div class="flex flex-col gap-2">
                                <input type="number" step="any" id="temp-value" class="border rounded px-3 py-2 w-full" placeholder="Nilai" />
                                <div class="flex gap-2">
                                    <select id="temp-from" class="border rounded px-2 py-2 w-1/2">
                                        <option value="c">Celsius</option>
                                        <option value="f">Fahrenheit</option>
                                        <option value="k">Kelvin</option>
                                    </select>
                                    <span class="flex items-center px-2">→</span>
                                    <select id="temp-to" class="border rounded px-2 py-2 w-1/2">
                                        <option value="c">Celsius</option>
                                        <option value="f">Fahrenheit</option>
                                        <option value="k">Kelvin</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition w-full">Konversi</button>
                                <button type="button" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition w-full" onclick="resetTempConverter()">Reset</button>
                            </div>
                            <div class="flex gap-2 items-center mt-2">
                                <span class="font-semibold">Hasil:</span>
                                <span id="temp-result" class="text-red-700 font-bold"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
        <script src="assets/js/calculations.js"></script>
        <script>
            // Saat halaman dimuat, jika ada hasil (dari PHP PRG), muat ulang riwayat agar selalu terbaru
            <?php if (!empty($calc_result)): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    reloadHistory();
                    // Tampilkan hasil jika dialihkan dari PHP PRG
                    document.getElementById('calc-result-value').textContent = "<?= htmlspecialchars($calc_result) ?>";
                    document.getElementById('calc-result').style.display = '';
                });
            <?php endif; ?>
        </script>
</body>

</html>