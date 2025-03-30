<?php
// Danh sách các file log có sẵn (đã sắp xếp gọn)
$availableLogs = [
    'admin/admin-log.txt',
    'admin/api.txt',
    'admin/backup.txt',
    'admin/ban-logs.txt',
    'users/edit.txt',
    'users/log.txt'
];

// Lấy file được chọn từ GET
$selectedLog = isset($_GET['log']) ? $_GET['log'] : 'users/log.txt'; // Mặc định là log.txt

// Kiểm tra xem file có hợp lệ không
if (!in_array($selectedLog, $availableLogs)) {
    $selectedLog = 'users/log.txt'; // Nếu file không hợp lệ, mặc định là log.txt
}
?>
