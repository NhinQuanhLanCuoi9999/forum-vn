<?php
// Kiểm tra xem file config.php có tồn tại không
if (!file_exists('config.php')) {
    // Nếu không tồn tại, chuyển hướng đến trang setup.php
    header('Location: setup.php');
    exit; // Đảm bảo không có mã nào được thực thi sau khi chuyển hướng
}
?>