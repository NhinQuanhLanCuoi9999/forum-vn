<?php
// Đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra xem tên người dùng có phải là "admin" không
    if (strtolower($username) === 'admin') {
        // Không kiểm tra từ bậy bạ, có thể thực hiện các kiểm tra khác nếu cần
    } else {
        // Kiểm tra xem tên người dùng có chứa từ bậy bạ nào không
        $badwords = file('badwords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($badwords as $badword) {
            if (stripos($username, trim($badword)) !== false) {
                $_SESSION['error'] = "Không thể tạo tài khoản vì tên người dùng chứa từ không phù hợp. (" . htmlspecialchars($badword) . ")";
                header("Location: index.php");
                exit();
            }
        }
    }

    // Kiểm tra nếu hai mật khẩu không trùng khớp
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra tài khoản đã tồn tại
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Tài khoản đã tồn tại!";
        header("Location: index.php");
        exit();
    }

    // Tạo tài khoản mới nếu không có lỗi
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $_SESSION['success'] = "Đăng ký thành công!";
    logAction(action: "Đăng ký tài khoản: $username");

    // Chuyển hướng về trang chính sau khi đăng ký thành công
    header(header: "Location: index.php");
    exit();
}
?>