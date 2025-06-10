<?php
require_once 'functions/functions.php';
cek_session();

if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit();
}
