<?php
require_once 'functions.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = dbquery("SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($result) === 1) {
        // Fetch user data
        $user = mysqli_fetch_assoc($result);
        // Verify password
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['login'] = true; // Set session variable to indicate user is logged in
            $_SESSION['user']['id'] = $user['id'];
            // set flash message
            setFlash('auth', 'Login successful!', 'success');
            // Set session variables
            if (isset($_POST['remember'])) {
                // set cookies untuk 'remember me' functionality
                setcookie('id', $user['id'], time() + 600, '/', '', false, true);
                setcookie('key', hash("sha256", $user["username"]), time() + 600, '/', '', false, true);
            }
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
