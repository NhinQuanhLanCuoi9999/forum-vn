<?php
// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    // Nếu người dùng chưa đăng nhập, chuyển hướng về index.php
    header("Location: index.php");
    exit(); // Đảm bảo dừng thực thi mã sau khi chuyển hướng
}
?>