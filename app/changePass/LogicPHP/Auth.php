<?php
include('../config.php');
session_start();
// Kiểm tra nếu người dùng chưa đăng nhập, chuyển hướng về index.php
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Kiểm tra nếu người dùng bị cấm
$username = $_SESSION['username'];
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
$stmt->bind_param("ss", $username, $ip);
$stmt->execute();
$result = $stmt->get_result();

// Nếu có thông tin về lệnh cấm, chuyển hướng đến warning.php
if ($result->num_rows > 0) {
    header("Location: warning.php");
    exit();
}

// Kiểm tra captcha
if (!isset($_COOKIE['captcha_verified']) || $_COOKIE['captcha_verified'] != 'true') {
    header("Location: ../src/captcha_verification.php");
    exit();
}

?>