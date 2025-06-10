<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Validate user ID
    if (empty($user_id)) {
        setFlash('auth', 'ID pengguna diperlukan.', 'error');
        header('Location: ../index.php');
        exit;
    }
    // Start transaction
    mysqli_begin_transaction($mysqli);

    try {
        // Ambil nama file profil user (jika ada)
        $result = dbquery("SELECT profile_pic FROM users WHERE id = $user_id");
        $user = mysqli_fetch_assoc($result);
        if ($user && !empty($user['profile_pic'])) {
            $file_path = __DIR__ . '/../assets/profiles/' . $user['profile_pic'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Delete from related tables first
        dbquery("DELETE FROM calculations WHERE user_id = $user_id");
        dbquery("DELETE FROM categories_expenses WHERE user_id = $user_id");
        dbquery("DELETE FROM expenses WHERE user_id = $user_id");
        dbquery("DELETE FROM todos WHERE user_id = $user_id");

        // Delete user
        dbquery("DELETE FROM users WHERE id = $user_id");

        mysqli_commit($mysqli);
        setFlash('auth', 'Akun dan data terkait berhasil dihapus.', 'success');
    } catch (Exception $e) {
        mysqli_rollback($mysqli);
        setFlash('auth', 'Gagal menghapus akun dan data terkait.', 'error');
    }

    session_unset();
    session_destroy();
    setcookie('id', '', time() + 3600, '/');
    setcookie('key', '', time() + 3600, '/');
    header('Location: ../login_page.php');
    exit;
}
