<?php
// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['username'])) {
    // Nếu chưa có session username => chưa đăng nhập
    $userLoggedIn = false;
    $isVerified = false;
} else {
    $userLoggedIn = true;
    // Kiểm tra trạng thái xác minh của tài khoản
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $isVerified = ($result && $result['is_active'] == 1);
}
?>