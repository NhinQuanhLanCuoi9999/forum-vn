<?php
if (isset($_GET['unban'])) {
    $ban_id = $_GET['unban'];

    // Lấy thông tin username và ip_address trước khi xóa
    $stmt = $conn->prepare("SELECT ip_address, username FROM bans WHERE id = ?");
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();
    $stmt->bind_result($ip_address, $username);
    $stmt->fetch();
    $stmt->close(); // Đóng statement sau khi lấy dữ liệu

    // Xóa bản ghi cấm
    $stmt = $conn->prepare("DELETE FROM bans WHERE id = ?");
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();
    $stmt->close();

    $success_message = "Đã hủy cấm thành công.";

    // Ghi log hủy cấm vào file
    $log_file = $_SERVER['DOCUMENT_ROOT'] . '/logs/ban-logs.txt';
    $log_message = "[" . date('Y-m-d H:i:s') . "] Hủy cấm IP: $ip_address, Username: $username";

    // Tạo thư mục logs nếu chưa có
    if (!file_exists(dirname($log_file))) {
        mkdir(dirname($log_file), 0777, true);
    }

    // Ghi log vào file
    file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND);

    // Chuyển hướng về trang hiện tại để tránh reload gây xóa nhiều lần
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Lấy danh sách các lệnh cấm
$bans = $conn->query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.username = users.username");
?>
