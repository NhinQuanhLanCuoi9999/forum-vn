<?php
$username = $_SESSION['username'] ?? null;

// Sử dụng prepared statements để tránh SQL Injection
$stmt = $conn->prepare("SELECT `2fa`, is_active FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $twofa = $row['2fa'];
    $is_active = $row['is_active'];

    if ($is_active == 0) {
        $update_stmt = $conn->prepare("UPDATE users SET `2fa` = 0 WHERE username = ?");
        $update_stmt->bind_param("s", $username);
        if ($update_stmt->execute()) {
            $twofa = 0;
        } else {
            error_log("Lỗi khi cập nhật 2FA: " . $conn->error);
        }
        $update_stmt->close();
    }

    if ($twofa == 1 && (!isset($_SESSION['2fa']) || $_SESSION['2fa'] != 1)) {
        header("Location: src/2fa.php");
        exit();
    }
}

$stmt->close();
?>
