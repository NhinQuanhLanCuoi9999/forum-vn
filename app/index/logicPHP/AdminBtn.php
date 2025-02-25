<?php
// Hiển thị nút chuyển hướng đến admin.php nếu username là 'admin'
if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    echo '<a href="admin_tool/admin.php" class="admin-button">Admin Panel</a>';
}
?>