<?php
// Đăng xuất
if (isset($_GET['logout'])) {
    setcookie("username", "", time() - 3600, "/"); // Xóa cookie
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}
?>