<?php
// Danh sách các file log có sẵn
$availableLogs = ['ban-log.txt', 'edit.txt', 'log.txt', 'logs.txt'];

// Lấy file được chọn từ GET
$selectedLog = isset($_GET['log']) ? $_GET['log'] : 'logs.txt'; // Mặc định là logs.txt

// Kiểm tra xem file có hợp lệ không
if (!in_array($selectedLog, $availableLogs)) {
    $selectedLog = 'logs.txt'; // Nếu file không hợp lệ, mặc định là logs.txt
}
         
            ?>