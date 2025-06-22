<?php
require_once 'functions.php';

// pastikan ada session login
if (!isset($_SESSION['login']) || !$_SESSION['login']) {
    setFlash('auth', 'Anda harus login terlebih dahulu.', 'error');
    header('Location: ../login_page.php');
    exit;
}

// Cek jika request method adalah POST dan user_id tersedia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Validasi user ID
    if (empty($user_id)) {
        setFlash('auth', 'ID pengguna diperlukan.', 'error');
        header('Location: ../login_page.php');
        exit;
    }

    // Mulai transaksi database
    mysqli_begin_transaction($mysqli);

    try {
        // Ambil nama file foto profil user (jika ada)
        $result = dbquery("SELECT profile_pic FROM users WHERE id = $user_id");
        $user = mysqli_fetch_assoc($result);
        if ($user && !empty($user['profile_pic'])) {
            $file_path = __DIR__ . '/../assets/profiles/' . $user['profile_pic'];
            // Hapus file foto profil jika file ada
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Hapus data user dari tabel-tabel terkait terlebih dahulu
        dbquery("DELETE FROM calculations WHERE user_id = $user_id");
        dbquery("DELETE FROM categories_expenses WHERE user_id = $user_id");
        dbquery("DELETE FROM expenses WHERE user_id = $user_id");
        dbquery("DELETE FROM todos WHERE user_id = $user_id");

        // Hapus data user dari tabel users
        dbquery("DELETE FROM users WHERE id = $user_id");

        // Commit transaksi jika semua query berhasil
        mysqli_commit($mysqli);
        setFlash('auth', 'Akun dan data terkait berhasil dihapus.', 'success');
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        mysqli_rollback($mysqli);
        setFlash('auth', 'Gagal menghapus akun dan data terkait.', 'error');
    }

    // Hapus semua data session
    session_unset();
    session_destroy();

    // Hapus cookie login
    setcookie('id', '', time() + 3600, '/');
    setcookie('key', '', time() + 3600, '/');

    // Redirect ke halaman login
    header('Location: ../login_page.php');
    exit;
}
