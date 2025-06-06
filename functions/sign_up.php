<?php
require_once 'functions.php';

if (isset($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $city = $_POST['city'];

    $password = password_hash($password, PASSWORD_DEFAULT);

    $result = dbquery("INSERT INTO users (username, fullname, email, city, password_hash) VALUES ('$username', '$fullname', '$email', '$city', '$password')");
    if ($result) {
        setFlash('register', 'Data member berhasil disimpan!', 'success'); // Set flash message untuk simpan
    } else {
        setFlash('register', 'Data member gagal disimpan!', 'error'); // Set flash message untuk simpan
    }
}
header("Location: ../register.php");
