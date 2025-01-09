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
        if (password_verify($password, $user['password'])) {
            // Đăng nhập thành công, lưu thông tin vào session
            $_SESSION['username'] = $username;

            logAction("Đăng nhập thành công: $username");

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
