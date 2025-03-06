<?php
// Dùng Prepared Statement để tránh SQL Injection
$username = $_SESSION['username'];
$sql = "SELECT `2fa`, is_active FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $twofa = $row['2fa'];
    $is_active = $row['is_active'];

    // Nếu tài khoản chưa active, vô hiệu hóa 2FA
    if ($is_active == 0) {
        $update_sql = "UPDATE users SET `2fa` = 0 WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("s", $username);
        $update_stmt->execute();
        $twofa = 0;
    }

    // Nếu 2FA bật nhưng session chưa có 2FA, buộc xác minh 2FA
    if ($twofa == 1 && (!isset($_SESSION['2fa']) || $_SESSION['2fa'] != 1)) {
        header("Location: src/2fa.php");
        exit();
    }
}

// Giải phóng bộ nhớ
$stmt->close();
$conn->close();
?>
