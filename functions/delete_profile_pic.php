<?php
// delete_profile_pic.php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Ambil nama file foto profil user
    $result = dbquery("SELECT profile_pic FROM users WHERE id = $user_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($result)) {
        $profile_pic = $row['profile_pic'];

        if (!empty($profile_pic)) {
            $file_path = '../assets/profiles/' . $profile_pic;

            // Hapus file jika ada
            if (file_exists($file_path)) {
                @unlink($file_path);
            }

            // Update database, set profile_pic ke String kosong
            dbquery("UPDATE users SET profile_pic = '' WHERE id = $user_id");
            setFlash('edit_profile', 'Foto profil berhasil dihapus.', 'success');
        }
    }
}

// Redirect kembali ke halaman profil (ganti dengan path yang sesuai)
header('Location: ../profile.php');
exit;
