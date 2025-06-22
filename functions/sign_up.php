<?php
require_once 'functions.php';

if (isset($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $konfirmasi = $_POST['confirm'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $city = $_POST['city'];

    // Validasi input
    if (empty($username) || empty($password) || empty($fullname) || empty($email) || empty($city)) {
        setFlash('auth', 'Semua field harus diisi!', 'error');
        header("Location: ../register.php");
        exit;
    }
    // Cek apakah username sudah ada
    $check = dbquery("SELECT id FROM users WHERE username = '$username'");
    if ($check && mysqli_num_rows($check) > 0) {
        setFlash('auth', 'Username sudah terdaftar!', 'error');
        header("Location: ../register.php");
        exit;
    }
    // cek username harus berawalan huruf
    if (!preg_match('/^[a-zA-Z]/', $username)) {
        setFlash('auth', 'Username harus diawali dengan huruf!', 'error');
        header("Location: ../register.php");
        exit;
    }
    // Cek apakah password dan konfirmasi password sama
    if ($password !== $konfirmasi) {
        setFlash('auth', 'Password dan Konfirmasi Password tidak sama!', 'error');
        header("Location: ../register.php");
        exit;
    }
    // cek apakah email sudah terdaftar
    $emailCheck = dbquery("SELECT id FROM users WHERE email = '$email'");
    if ($emailCheck && mysqli_num_rows($emailCheck) > 0) {
        setFlash('auth', 'Email sudah terdaftar!', 'error');
        header("Location: ../register.php");
        exit;
    }

    // Hash password
    $password = password_hash($password, PASSWORD_DEFAULT);

    // Insert data ke database
    $result = dbquery("INSERT INTO users (username, fullname, email, city, password_hash, profile_pic) VALUES ('$username', '$fullname', '$email', '$city', '$password', '')");
    if ($result) {
        setFlash('auth', 'Berhasil Daftar Akun, Silahkan Login!', 'success');
    } else {
        setFlash('auth', 'Gagal Daftar Akun!', 'error');
    }
}
header("Location: ../register.php");
