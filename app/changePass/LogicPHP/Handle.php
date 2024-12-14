<?php
// Xử lý thay đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $username = $_SESSION['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới với xác nhận
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
        header("Location: change_password.php");
        exit();
    }

    // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($current_password, $user['password'])) {
        // Mã hóa mật khẩu mới
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu trong cơ sở dữ liệu
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $username);
        $stmt->execute();
        
        // Thông báo thành công và chuyển hướng lại change_password.php
        $_SESSION['success'] = "Mật khẩu đã được thay đổi thành công!";
        header("Location: change_password.php");
        exit();
    } else {
        $_SESSION['error'] = "Mật khẩu hiện tại không đúng!";
        header("Location: change_password.php");
        exit();
    }
}
?>