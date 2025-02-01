<?php
// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_SESSION['username'];
}

// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}
?>