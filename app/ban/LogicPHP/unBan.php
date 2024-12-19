<?php
if (isset($_GET['unban'])) {
    $ban_id = $_GET['unban'];

    // Lấy thông tin username và ip_address trước khi xóa
    $stmt = $conn->prepare("SELECT ip_address, username FROM bans WHERE id = ?");
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();
    $stmt->bind_result($ip_address, $username);
    $stmt->fetch();

    // Giải phóng kết quả truy vấn trước khi thực hiện truy vấn DELETE
    $stmt->free_result(); 

    // Xóa bản ghi cấm
    $stmt = $conn->prepare("DELETE FROM bans WHERE id = ?");
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();

    $success_message = "Đã hủy cấm thành công.";

    // Ghi log hủy cấm với ip_address và username
    writeLog("Hủy cấm IP: $ip_address, Username: $username");

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$bans = $conn->query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.username = users.username");
?>