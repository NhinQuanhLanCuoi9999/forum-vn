<?php
// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['username'])) {
    // Lấy giá trị username từ session
    $username = $_SESSION['username'];

    // Dùng prepared statement để lấy giá trị 2fa và is_active
    $stmt = $conn->prepare("SELECT `2fa`, is_active FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($twofa, $is_active);
    $stmt->fetch();
    $stmt->close();

    // Nếu tài khoản bị vô hiệu hóa, đặt 2FA về 0
    if ($is_active === 0) {
        $stmt = $conn->prepare("UPDATE users SET `2fa` = 0 WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute(); 
        $stmt->close();
    }
    

    // Nếu 2FA bật nhưng session chưa xác nhận, yêu cầu xác thực 2FA
    if ($twofa == 1 && (!isset($_SESSION['2fa']) || $_SESSION['2fa'] != 1)) {
        header("Location: ../src/2fa.php");
        exit();
    }
}
?>
