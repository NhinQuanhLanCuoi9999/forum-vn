<?php
include '../config.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra đăng nhập admin
if (isset($_COOKIE['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>
