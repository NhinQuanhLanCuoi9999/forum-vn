<?php
// Đăng xuất
if (isset($_GET['logout'])) {
    // Xóa chỉ session username
    unset($_SESSION['username']);
    unset($_SESSION['2fa']);
    logAction("Đăng xuất: {$_SESSION['username']}");
    header("Location: index.php");
    exit();
}
?>
