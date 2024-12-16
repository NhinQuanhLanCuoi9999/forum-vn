<?php
// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$log_file = '../logs/ban-log.txt';

// Kiểm tra xem hàm writeLog đã được định nghĩa chưa
if (!function_exists('writeLog')) {
    function writeLog($message) {
        global $log_file;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }
}

$error_message = '';
$success_message = ''; // Khởi tạo thông báo thành công
?>