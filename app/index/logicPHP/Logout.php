<?php
// Đăng xuất
if (isset($_GET['logout'])) {
    // Xóa cookie
    setcookie("username", "", time() - 3600, "/");
    session_unset();
    session_destroy();
    logAction("Đăng xuất: {$_SESSION['username']}");
    header("Location: index.php");
    exit();
}
?>