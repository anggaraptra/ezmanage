<?php
require_once 'functions/functions.php';
cek_session();

// Dummy data, ganti dengan query database Anda
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EzManage - Todo List</title>
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
                <a href="todo.php" class="flex items-center gap-2 px-3 py-2 rounded bg-white bg-opacity-10 hover:bg-opacity-20 transition">
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
                <h1 class="text-2xl font-bold text-blue-600">Todo List</h1>
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
                    <p class="text-gray-500">Selamat datang di halaman Todo List! Semangat menyelesaikan tugas-tugasmu hari ini 🎉</p>
                </div>
                <!-- Todo List -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-blue-600">Todo List</h3>
                        <a href="#" onclick="openModal('add')" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">+ Tambah Todo</a>
                    </div>
                    <!-- Filter & Search Form -->
                    <form method="get" class="flex flex-wrap gap-3 mb-4 items-end">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Status</label>
                            <select name="status" class="border rounded px-2 py-1">
                                <option value="">Semua</option>
                                <option value="Belum" <?= $status_filter == 'Belum' ? 'selected' : '' ?>>Belum</option>
                                <option value="Proses" <?= $status_filter == 'Proses' ? 'selected' : '' ?>>Proses</option>
                                <option value="Selesai" <?= $status_filter == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Jatuh Tempo</label>
                            <input type="date" name="due" value="<?= htmlspecialchars($due_filter) ?>" class="border rounded px-2 py-1">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Cari</label>
                            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Judul/Deskripsi" class="border rounded px-2 py-1">
                        </div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition">Filter</button>
                        <a href="todo.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300 transition">Reset</a>
                    </form>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Deskripsi</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Jatuh Tempo</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
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
                                    $parts = explode('-', $tanggal);
                                    if (count($parts) == 3) {
                                        return $parts[2] . ' ' . $bulan[(int)$parts[1]] . ' ' . $parts[0];
                                    }
                                    return htmlspecialchars($tanggal);
                                }
                                $i = 1;
                                foreach ($filtered_todos as $todo): ?>
                                    <tr>
                                        <td class="px-4 py-2"><?= $i++ ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($todo['title']) ?></td>
                                        <td class="px-4 py-2"><?= htmlspecialchars($todo['description']) ?></td>
                                        <td class="px-4 py-2"><?= format_tanggal($todo['due_date']) ?></td>
                                        <td class="px-4 py-2">
                                            <?php if ($todo['status'] == 'Selesai'): ?>
                                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Selesai</span>
                                            <?php elseif ($todo['status'] == 'Proses'): ?>
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs">Proses</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Belum</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-2 flex gap-2">
                                            <button onclick="openModal('edit', <?= htmlspecialchars(json_encode($todo)) ?>)" class="bg-yellow-400 hover:bg-yellow-500 text-white px-2 py-1 rounded text-xs">Edit</button>
                                            <button onclick="openModal('delete', <?= $todo['id'] ?>)" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Hapus</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($filtered_todos)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-400 py-4">Tidak ada tugas ditemukan.</td>
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
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <button onclick="closeModal()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700">&times;</button>
            <?= handle_todo_form_submit(); ?>
            <form id="todo-form" method="post" action="">
                <input type="hidden" name="id" id="todo-id">
                <h2 id="modal-title" class="text-xl font-bold mb-4">Tambah Todo</h2>
                <div class="mb-3">
                    <label class="block text-gray-700 mb-1">Judul</label>
                    <input type="text" name="title" id="todo-title" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="todo-desc" class="w-full border rounded px-3 py-2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-gray-700 mb-1">Tanggal Jatuh Tempo</label>
                    <input type="date" name="due_date" id="todo-due" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3" id="status-group" style="display:none;">
                    <label class="block text-gray-700 mb-1">Status</label>
                    <select name="status" id="todo-status" class="w-full border rounded px-3 py-2">
                        <option value="Belum">Belum</option>
                        <option value="Proses">Proses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Batal</button>
                    <button type="submit" id="modal-submit" class="px-4 py-2 rounded bg-blue-500 text-white hover:bg-blue-600">Simpan</button>
                </div>
            </form>
            <!-- Hapus Modal -->
            <div id="delete-group" class="hidden">
                <p class="mb-4 text-gray-700">Yakin ingin menghapus tugas ini?</p>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">Batal</button>
                    <?= handle_hapus_todo(); ?>
                    <form id="delete-form" method="post" action="">
                        <input type="hidden" name="delete_id" id="delete-id">
                        <button type="submit" class="px-4 py-2 rounded bg-red-500 text-white hover:bg-red-600">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/todo.js"></script>
</body>

</html>