<?php
require_once 'functions/functions.php';
cek_session();

// Jika pengguna sudah login, arahkan ke halaman dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php"); // Redirect ke dashboard
    exit();
}
