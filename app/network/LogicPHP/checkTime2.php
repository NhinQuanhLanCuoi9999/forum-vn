<?php
// Cập nhật thời gian truy cập hiện tại
$_SESSION['last_access_time'] = time();
// log.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $ip = filter_var($data['ip'], FILTER_VALIDATE_IP); // Lọc địa chỉ IP

    // Ghi địa chỉ IP vào tệp log
    $logFile = 'network-logs.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "$timestamp - $ip\n";

    // Kiểm tra xem tệp có thể ghi được không và thực hiện ghi log
    if (is_writable($logFile)) {
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    } else {
        error_log("Không thể ghi vào tệp $logFile");
    }
}
?>