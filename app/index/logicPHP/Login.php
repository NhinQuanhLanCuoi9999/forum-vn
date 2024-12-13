<?php
// Đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify(password: $password, hash: $user['password'])) {
            $_SESSION['username'] = $username;

            // Thiết lập cookie để ghi nhớ người dùng trong 30 ngày
            setcookie(name: "username", value: $username, expires_or_options: time() + (30 * 24 * 60 * 60), path: "/");
            logAction(action: "Đăng nhập thành công: $username");

            // Chuyển hướng sau khi đăng nhập
            header(header: "Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Mật khẩu không chính xác!";
        }
    } else {
        $_SESSION['error'] = "Tài khoản không tồn tại!";
    }
}
?>