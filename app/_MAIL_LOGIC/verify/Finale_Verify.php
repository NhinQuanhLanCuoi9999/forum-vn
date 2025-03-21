<?php
// --------------------
// XỬ LÝ XÁC MINH KHI NHẤN VÀO LIÊN KẾT
// --------------------
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    if (isset($_SESSION['verification_code'], $_SESSION['user_gmail'], $_SESSION['verification_time']) && $code === $_SESSION['verification_code']) {
        // Kiểm tra thời gian: nếu mã đã được tạo trong vòng 15 phút (900 giây)
        if (time() - $_SESSION['verification_time'] <= 900) {
            // Cập nhật trường is_active thành '1' cho người dùng có gmail trong session
            $stmt = $conn->prepare("UPDATE users SET is_active = '1' WHERE gmail = ?");
            if (!$stmt) {
                die("Lỗi chuẩn bị truy vấn: " . $conn->error);
            }
            $stmt->bind_param("s", $_SESSION['user_gmail']);
            if ($stmt->execute()) {
                $activation_status = "Email của bạn đã được xác minh thành công!";
                // Xóa các biến liên quan đến xác minh sau khi thành công
                unset($_SESSION['verification_code'], $_SESSION['verification_time'], $_SESSION['user_gmail']);
                // Lấy URL hiện tại không bao gồm tham số GET
                $currentUrl = strtok($_SERVER["REQUEST_URI"], '?');
                // Chuyển hướng tự động sau 3 giây về trang hiện tại
                header("refresh:3;url=" . $currentUrl);
            } else {
                $activation_status = "Lỗi khi cập nhật trạng thái tài khoản.";
            }
            $stmt->close();
        } else {
            $activation_status = "Mã xác minh đã hết hạn. Vui lòng gửi lại yêu cầu xác minh.";
        }
    } else {
        $activation_status = "Mã xác minh không hợp lệ.";
    }
}
?>
