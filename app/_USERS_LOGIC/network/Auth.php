<?php
// Kiểm tra nếu đã có thời gian truy cập lần trước
if (isset($_SESSION['last_access_time'])) {
    $lastAccessTime = $_SESSION['last_access_time'];
    $currentTime = time();
    $timeElapsed = $currentTime - $lastAccessTime;

    // Nếu thời gian truy cập chưa hết 5 phút (300 giây)
    if ($timeElapsed < 300) {
        // Chuyển hướng về trang index.php và báo lỗi
        $_SESSION['error'] = "Bạn chỉ có thể truy cập vào cấu hình mạng 5 phút / 1 lần.";
        header('Location: index.php');
        exit();
    }
}

// Cập nhật thời gian truy cập hiện tại
$_SESSION['last_access_time'] = time();
?>