<?php
require_once 'functions.php';
// Hapus semua session
session_unset();
// Hancurkan session
session_destroy();

header("Location: ../login_page.php");
exit();
