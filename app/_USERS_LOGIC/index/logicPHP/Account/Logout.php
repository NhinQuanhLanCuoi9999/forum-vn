<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/app/_USERS_LOGIC/index/logicPHP/Logs/Log.php');


// Đăng xuất
if (isset($_GET['logout'])) {
    logAction("Đăng xuất");

    unset($_SESSION['username']);
    unset($_SESSION['2fa']);
    unset($_SESSION['role']);
    unset($_SESSION['reset_email']);
    unset($_SESSION['otp_expiry']);
    unset($_SESSION['step']);
    unset($_SESSION['captcha_verified']);
    unset($_SESSION['verification_code']);
    unset($_SESSION['verification_time']);

    // Chuyển hướng về trang chính
    header("Location: index.php");
    exit();
}
?>
