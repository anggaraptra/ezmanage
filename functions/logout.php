<?php
require_once 'functions.php';
// Hapus semua session
session_unset();
// Hancurkan session
session_destroy();
// Hapus cookie jika ada
setcookie('id', '', time() + 3600, '/');
setcookie('key', '', time() + 3600, '/');

header("Location: ../login_page.php");
exit();
