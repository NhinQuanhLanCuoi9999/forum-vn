<?php
// Kiểm tra nếu người dùng đã đăng nhập thông qua cookie
if (isset($_COOKIE['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}

// Hiển thị nút chuyển hướng đến admin.php nếu username là 'admin'
if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    echo '<a href="admin.php" class="admin-button">Admin Panel</a>';
}
?>