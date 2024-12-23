<?php
session_start();
include('config.php');

// Kiểm tra trạng thái cấm trước khi cho phép truy cập vào index.php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Kiểm tra nếu username đã đăng nhập
if ($username) {
    // Chuẩn bị câu truy vấn kiểm tra xem có bản ghi nào trong bảng bans với username hoặc ip_address
    $stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
    $stmt->bind_param("ss", $username, $ip_address); // Liên kết biến với câu truy vấn
    $stmt->execute(); // Thực thi câu truy vấn
    $result = $stmt->get_result(); // Lấy kết quả từ câu truy vấn

    // Kiểm tra nếu có bản ghi nào (người dùng bị cấm)
    if ($result->num_rows > 0) {
        // Nếu bị cấm, chuyển hướng đến warning.php
        header("Location: warning.php");
        exit(); // Dừng mọi mã PHP còn lại
    }
}

// Hủy session
session_unset();
session_destroy();

// Xóa cookie liên quan đến đăng nhập (ví dụ: username hoặc session token)
if (isset($_COOKIE['captcha_verified'])) {
    setcookie('captcha_verified', '', time() - 3600, '/'); // Xóa cookie captcha_verified
}

if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, '/'); // Xóa cookie username (nếu có)
}

// Chuyển hướng về trang index
header("Location: index.php");
exit();
?>
