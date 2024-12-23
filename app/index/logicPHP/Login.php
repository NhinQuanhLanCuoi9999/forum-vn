<?php
// Đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Kết nối tới cơ sở dữ liệu và truy vấn
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify(password: $password, hash: $user['password'])) {
            // Đăng nhập thành công, lưu thông tin vào session
            $_SESSION['username'] = $username;

            // Tạo một mã thông báo phiên (session token) thay vì lưu tên người dùng trực tiếp trong cookie
            $sessionToken = bin2hex(random_bytes(32)); // Tạo mã thông báo ngẫu nhiên
            $_SESSION['token'] = $sessionToken;

            // Thiết lập cookie với các thuộc tính bảo mật (không sử dụng samesite)
            setcookie(
                "username", 
                $sessionToken, 
                time() + (30 * 24 * 60 * 60), // Thời gian hết hạn cookie (30 ngày)
                "/", // Đường dẫn của cookie
                "", // Domain (để trống khi dùng localhost hoặc 127.0.0.1)
                true, // Secure (chỉ gửi qua HTTPS)
                true // HttpOnly (ngăn JavaScript truy cập cookie)
            );

            logAction(action: "Đăng nhập thành công: $username");

            // Chuyển hướng sau khi đăng nhập
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Mật khẩu không chính xác!";
        }
    } else {
        $_SESSION['error'] = "Tài khoản không tồn tại!";
    }
}
?>
