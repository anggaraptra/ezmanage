<?php
require_once 'functions/functions.php';
cek_session();

if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
