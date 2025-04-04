<?php
function writeLog($id, $content, $type) {
    $log_dir = $_SERVER['DOCUMENT_ROOT'] . "/logs/admin/";
    $log_file = $log_dir . "admin-log.txt";

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    $user_name = $_SESSION['username'] ?? 'Unknown';
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $log_entry = "[" . date("d/m/Y | H:i:s") . "] Người dùng : [$user_name] (IP: $user_ip) đã thao tác xóa $type có ID [$id] ( nội dung : [$content])\n";
    @file_put_contents($log_file, $log_entry, FILE_APPEND);
}
?>