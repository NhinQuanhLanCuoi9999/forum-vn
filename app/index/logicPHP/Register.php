<?php

// Kiểm tra CSRF token khi nhận dữ liệu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Tiến hành xử lý đăng ký (các bước như kiểm tra tài khoản, mật khẩu... như mã trước đó)
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gmail = trim($_POST['gmail']); // Lấy dữ liệu từ trường email (gmail)

    // Kiểm tra tên người dùng và các điều kiện khác như trước
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $_SESSION['error'] = "Tên người dùng chỉ được chứa chữ cái, số và dấu gạch dưới.";
        header("Location: index.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra tài khoản đã tồn tại trong cơ sở dữ liệu
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ? OR gmail = ?");
    $checkUser->bind_param("ss", $username, $gmail);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Tài khoản hoặc email đã tồn tại!";
        header("Location: index.php");
        exit();
    }

    // Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Tạo tài khoản mới nếu không có lỗi
    $stmt = $conn->prepare("INSERT INTO users (username, password, gmail) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $gmail); // Lưu thêm giá trị gmail
    $stmt->execute();

    // Ghi log hành động
    logAction("Đăng ký tài khoản: " . htmlspecialchars($username));

    // Thông báo thành công và chuyển hướng về trang chính
    $_SESSION['success'] = "Đăng ký thành công!";
    header("Location: index.php");
    exit();
}
?>
