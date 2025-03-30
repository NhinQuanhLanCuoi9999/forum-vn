<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unban'])) {
    
    if (!isset($_SESSION['role'])) {
        $error_message = "Bạn không có quyền thực hiện thao tác này!";
    } else {
        $user_role = $_SESSION['role']; // Lấy role từ session
        $ban_id = $_POST['unban'];

        // Lấy thông tin username và ip_address trước khi xóa
        $stmt = $conn->prepare("SELECT ip_address, username FROM bans WHERE id = ?");
        $stmt->bind_param("i", $ban_id);
        $stmt->execute();
        $stmt->bind_result($ip_address, $username);
        $stmt->fetch();
        $stmt->close();
        
        if ($username) {
            // Kiểm tra role của người bị cấm
            $stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $banned_user_role = $result->num_rows > 0 ? $result->fetch_assoc()['role'] : 'member';
            $stmt->close();
        } else {
            $banned_user_role = 'member'; // Nếu chỉ cấm theo IP thì coi như member
        }

        // Kiểm tra quyền unban
        if ($user_role === 'owner' || ($user_role === 'admin' && $banned_user_role === 'member')) {
            // Xóa bản ghi cấm
            $stmt = $conn->prepare("DELETE FROM bans WHERE id = ?");
            $stmt->bind_param("i", $ban_id);
            $stmt->execute();
            $stmt->close();

            $success_message = "Đã hủy cấm thành công.";

            // Ghi log hủy cấm vào file
            $log_file = $_SERVER['DOCUMENT_ROOT'] . '/logs/admin/ban-logs.txt';
            $log_message = "[" . date('Y-m-d H:i:s') . "] Hủy cấm IP: $ip_address, Username: $username";

            // Tạo thư mục logs nếu chưa có
            if (!file_exists(dirname($log_file))) {
                mkdir(dirname($log_file), 0777, true);
            }

            // Ghi log vào file
            file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND);
        } else {
            $error_message = "Bạn không có quyền hủy cấm người dùng này!";
        }
    }
}

// Lấy danh sách các lệnh cấm
$bans = $conn->query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.username = users.username");
?>
