<?php

require_once 'Log.php';

// Đăng xuất
if (isset($_GET['logout'])) {
    logAction("Đăng xuất");

    unset($_SESSION['username']);
    unset($_SESSION['2fa']);
    unset($_SESSION['role']);

    // Chuyển hướng về trang chính
    header("Location: index.php");
    exit();
}
?>
