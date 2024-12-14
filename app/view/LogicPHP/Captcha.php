<?php

// Kiểm tra nếu người dùng đã xác thực captcha bằng cookie
if (!isset($_COOKIE['captcha_verified'])) {
    header("Location: ../src/captcha_verification.php"); // Chuyển hướng đến trang xác thực captcha
    exit();
}
?>