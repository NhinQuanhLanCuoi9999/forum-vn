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

    // Kiểm tra tên người dùng và các điều kiện khác như trước
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $_SESSION['error'] = "Tên người dùng chỉ được chứa chữ cái, số và dấu gạch dưới.";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra mật khẩu và các điều kiện khác
    if (strlen($password) < 8) {
        $_SESSION['error'] = "Mật khẩu phải có ít nhất 8 ký tự.";
        header("Location: index.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra tài khoản đã tồn tại trong cơ sở dữ liệu
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Tài khoản đã tồn tại!";
        header("Location: index.php");
        exit();
    }

    // Mã hóa mật khẩu trước khi lưu vào cơ sở dữ liệu
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Tạo tài khoản mới nếu không có lỗi
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();

    // Ghi log hành động
    logAction("Đăng ký tài khoản: " . htmlspecialchars($username));

    // Thông báo thành công và chuyển hướng về trang chính
    $_SESSION['success'] = "Đăng ký thành công!";
    header("Location: index.php");
    exit();
}
