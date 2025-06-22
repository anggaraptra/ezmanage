<?php
require_once "functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    // Validasi input
    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        setFlash('forgot', 'Semua field harus diisi.', 'error');
        header('Location: ../forgot_password.php');
        exit();
    }

    // Validasi format email dan kecocokan password
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlash('forgot', 'Format email tidak valid.', 'error');
        header('Location: ../forgot_password.php');
        exit();
    }

    // Validasi kecocokan password dan konfirmasi
    if ($password !== $confirm) {
        setFlash('forgot', 'Konfirmasi password tidak cocok.', 'error');
        header('Location: ../forgot_password.php');
        exit();
    }

    // Cek apakah user dengan username dan email tersebut ada
    $usernameEsc = mysqli_real_escape_string($mysqli, $username);
    $emailEsc = mysqli_real_escape_string($mysqli, $email);
    $sql = "SELECT id FROM users WHERE username='$usernameEsc' AND email='$emailEsc' LIMIT 1";
    $result = dbquery($sql);

    // Cek apakah query berhasil
    if (mysqli_num_rows($result) === 0) {
        setFlash('forgot', 'Username atau email tidak ditemukan.', 'error');
        header('Location: ../forgot_password.php');
        exit();
    }

    // Update password
    $user = mysqli_fetch_assoc($result);
    $userId = $user['id'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update password hash in the database
    $updateSql = "UPDATE users SET password_hash='$hashedPassword' WHERE id='$userId'";
    if (dbquery($updateSql)) {
        setFlash('forgot', 'Password berhasil diubah. Silakan login dengan password baru.', 'success');
        header('Location: ../forgot_password.php');
        exit();
    } else {
        setFlash('forgot', 'Terjadi kesalahan saat mengubah password.', 'error');
        header('Location: ../forgot_password.php');
        exit();
    }
} else {
    header('Location: ../forgot_password.php');
    exit();
}
