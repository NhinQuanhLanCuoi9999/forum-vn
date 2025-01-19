<?php
// Đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    // Kiểm tra và làm sạch dữ liệu đầu vào
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Kiểm tra nếu dữ liệu đầu vào hợp lệ
    if (!preg_match("/^[a-zA-Z0-9_]{5,30}$/", $username)) {
        $_SESSION['error'] = "Tên đăng nhập không hợp lệ.";
        header("Location: index.php");
        exit();
    }

    // Đảm bảo rằng mật khẩu phải dài hơn 6 ký tự
    if (strlen($password) < 6) {
        $_SESSION['error'] = "Mật khẩu quá ngắn.";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra số lần thử đăng nhập trong session hoặc DB
    if (isset($_SESSION['failed_attempts']) && $_SESSION['failed_attempts'] >= 5) {
        $_SESSION['error'] = "Bạn đã thử đăng nhập quá nhiều lần. Vui lòng thử lại sau.";
        header("Location: index.php");
        exit();
    }

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
            $_SESSION['last_login_time'] = time(); // Ghi lại thời gian đăng nhập

            // Reset số lần thử đăng nhập
            $_SESSION['failed_attempts'] = 0;

            // Log hành động
            logAction("Đăng nhập thành công: $username");

            // Chuyển hướng sau khi đăng nhập
            header("Location: index.php");
            exit();
        } else {
            // Nếu mật khẩu sai, tăng số lần thử
            $_SESSION['failed_attempts'] = isset($_SESSION['failed_attempts']) ? $_SESSION['failed_attempts'] + 1 : 1;
            $_SESSION['error'] = "Tài khoản hoặc mật khẩu không chính xác!";
        }
    } else {
        $_SESSION['error'] = "Tài khoản hoặc mật khẩu không chính xác!";
    }
}

// Tạo và lưu CSRF token cho form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Tạo token ngẫu nhiên
?>