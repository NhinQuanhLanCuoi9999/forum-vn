<?php


// Tạo CSRF token nếu chưa có
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra CSRF token khi nhận dữ liệu POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    // Kiểm tra token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Lấy dữ liệu từ form
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $gmail = trim($_POST['gmail']);

    // Kiểm tra username có đủ 5 ký tự không
    if (strlen($username) < 5) {
        $_SESSION['error'] = "Tên người dùng phải có ít nhất 5 ký tự.";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra username chỉ chứa chữ, số, _
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $_SESSION['error'] = "Tên người dùng chỉ được chứa chữ cái, số và dấu gạch dưới.";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra password có đủ 6 ký tự không
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Mật khẩu phải có ít nhất 6 ký tự.";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra hai mật khẩu có trùng khớp không
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra email không được rỗng
    if (empty($gmail)) {
        $_SESSION['error'] = "Email không được để trống.";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra email hợp lệ
    if (!filter_var($gmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ.";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra tài khoản hoặc email đã tồn tại
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ? OR gmail = ?");
    $checkUser->bind_param("ss", $username, $gmail);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Tài khoản hoặc email đã tồn tại!";
        header("Location: index.php");
        exit();
    }

    // Mã hóa mật khẩu trước khi lưu vào database
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Lưu tài khoản vào database
    $stmt = $conn->prepare("INSERT INTO users (username, password, gmail) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $gmail);
    
    if ($stmt->execute()) {
        // Ghi log hành động đăng ký
        logAction("Đăng ký tài khoản: " . htmlspecialchars($username));

        // Thông báo thành công
        $_SESSION['success'] = "Đăng ký thành công!";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Lỗi khi tạo tài khoản, vui lòng thử lại.";
        header("Location: index.php");
        exit();
    }
}
?>
