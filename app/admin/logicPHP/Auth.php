<?php
if (!file_exists(__DIR__ . '/config.php')) {
    header('Location: ../setup.php');
    exit();
}
// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>