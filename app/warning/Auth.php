<?php
session_start();
include('../config.php');

// Thiết lập múi giờ của PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$ip = $_SERVER['REMOTE_ADDR'];

// Đặt múi giờ cho MySQL
$conn->query("SET time_zone = '+07:00'");

$stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
$stmt->bind_param("ss", $username, $ip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$ban = $result->fetch_assoc();
$reason = $ban['reason'];
$ban_end = $ban['ban_end'];

$now = new DateTime("now", new DateTimeZone('Asia/Ho_Chi_Minh'));
$ban_end_time = new DateTime($ban_end, new DateTimeZone('Asia/Ho_Chi_Minh'));

$ban_expired = false; // Khởi tạo giá trị mặc định

if ($ban_end_time < $now) {
    $ban_expired = true;
    $ban_end_display = '<strong style="color: red; font-weight: bold;">Đã hết hạn</strong>';
} else {
    $interval = $now->diff($ban_end_time);
    
    if ($interval->y >= 20) {
        $ban_end_display = '<strong style="color: red; font-weight: bold;">Vĩnh viễn</strong>';
    } elseif ($interval->d >= 7) {
        $ban_end_display = $ban_end_time->format('d/m/Y | H:i:s');
    } elseif ($interval->d > 0) {
        $ban_end_display = "Còn lại {$interval->d} ngày";
    } elseif ($interval->h > 0) {
        $ban_end_display = "Còn lại {$interval->h} giờ";
    } else {
        $ban_end_display = "Còn lại {$interval->i} phút";
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agree_terms']) && $_POST['agree_terms'] === '1') {
    $stmt = $conn->prepare("DELETE FROM bans WHERE (username = ? OR ip_address = ?) AND ban_end IS NOT NULL AND ban_end < NOW()");
    $stmt->bind_param("ss", $username, $ip);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Lệnh cấm đã được xóa thành công.";
        $redirect_after = true;
    } else {
        $message = "Đã xảy ra lỗi. Có thể là do khác múi giờ, hãy thử lại sau 6-12 tiếng.";
        $redirect_after = false;
    }
} else {
    $message = "";
    $redirect_after = false;
}
?>