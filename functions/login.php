<?php
require_once 'functions.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = dbquery("SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password_hash'])) {
            $_SESSION['login'] = true; // Set session variable to indicate user is logged in
            $_SESSION['user']['id'] = $user['id'];
            // set flash message
            setFlash('auth', 'Login successful!', 'success');
            // Set session variables
            if (isset($_POST['remember'])) {
                // Set cookie for remember me (set path and httponly for reliability)
                setcookie('id', $user['id'], time() + 600, '/', '', false, true);
                setcookie('key', hash("sha256", $user["username"]), time() + 600, '/', '', false, true);
            }
            header("Location: ../index.php");
            exit();
        } else {
            // set flash message
            setFlash('auth', 'Username or Password incorrect!', 'error');
        }
    } else {
        // set flash message
        setFlash('auth', 'Username or Password incorrect!', 'error');
    }
}
header("Location: ../login_page.php");
exit();
