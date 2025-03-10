<?php
if (!function_exists('logAction')) {
    function logAction($message) {
        error_log($message);
    }
}

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'owner'])) {
    die("Bạn không có quyền truy cập trang này.");
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$session_role = $_SESSION['role'];
$message = '';
?>