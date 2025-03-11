<?php
// Lấy thông tin của user
$stmt = $conn->prepare("SELECT gmail, is_active, `2fa` FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Xử lý khi form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra điều kiện: gmail khác null và is_active bằng 1
    if ($user['gmail'] !== null && $user['is_active'] == 1) {
        // Nếu checkbox không được gửi (tức bị bỏ chọn) => 2FA = 0
        $two_fa_status = (isset($_POST['switch2fa']) && $_POST['switch2fa'] === '1') ? 1 : 0;

        $update_stmt = $conn->prepare("UPDATE users SET `2fa` = ? WHERE username = ?");
        $update_stmt->bind_param("is", $two_fa_status, $username);
        $update_stmt->execute();
        $update_stmt->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>