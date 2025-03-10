<?php
// Ghi log vào tệp
function logAction($action) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $log_entry = date('Y-m-d H:i:s') . " - $ip_address: $action\n";
    file_put_contents('../logs/admin-log.txt', $log_entry, FILE_APPEND);
}
?>