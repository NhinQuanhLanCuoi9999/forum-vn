<?php
// Hàm ghi log
function logAction($action) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Khách';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] - IP: $ip - Người dùng: $username - Hành động: $action\n";

    // Đường dẫn tới file log
    $log_dir = '../htdocs/logs';
    $log_file = $log_dir . '/logs.txt';

    // Kiểm tra xem thư mục logs có tồn tại không, nếu không thì tạo mới
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true); // Tạo thư mục với quyền truy cập
    }

    // Ghi log vào file
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>