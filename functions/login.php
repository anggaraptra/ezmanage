<?php
require_once 'functions.php';

if (isset($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = dbquery("SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            // set flash message
            setFlash('auth', 'Login berhasil!', 'success');
            // Set session variables
            $_SESSION['user']['id'] = $user['id'];
            $_SESSION['user']['username'] = $user['username'];
            $_SESSION['user']['fullname'] = $user['fullname'];
            $_SESSION['user']['email'] = $user['email'];
            header("Location: ../index.php");
            exit();
        } else {
            // set flash message
            setFlash('auth', 'Username atau Password salah!', 'error');
        }
    } else {
        // set flash message
        setFlash('auth', 'Username atau Password salah!', 'error');
    }
}
header("Location: ../login_page.php");
exit();
