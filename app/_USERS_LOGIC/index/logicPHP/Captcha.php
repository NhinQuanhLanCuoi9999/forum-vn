<?php
// Kiểm tra nếu người dùng đã xác thực captcha bằng session
if (!isset($_SESSION['captcha_verified'])) {
    header("Location: ../src/captcha_verification.php"); // Chuyển hướng đến trang xác thực captcha
    exit();
}
?>