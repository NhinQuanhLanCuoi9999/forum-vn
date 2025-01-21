<?php
// Bắt đầu session nếu chưa có
session_start();

// Kiểm tra nếu không có session 'username'
if (!isset($_SESSION['username'])) {
    // Nếu không có session, chuyển hướng về trang index.php
    header("Location: ../index.php");
    exit(); // Dừng script tiếp theo sau khi chuyển hướng
}
?>
