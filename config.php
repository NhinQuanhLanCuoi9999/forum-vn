<?php
session_start();

$host = 'localhost'; // Địa chỉ máy chủ
$db = 'forum_db'; // Tên cơ sở dữ liệu
$user = 'root'; // Tên đăng nhập
$pass = ''; // Mật khẩu

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>