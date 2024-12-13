<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ban'])) {
    $username = trim($_POST['username']);
    $reason = trim($_POST['reason']);
    $ip_address = trim($_POST['ip_address']);
    $ban_end = $_POST['ban_end']; // Giá trị ban_end từ biểu mẫu
    $permanent = 0;

    // Kiểm tra định dạng ban_end
    $ban_end_date = DateTime::createFromFormat('Y-m-d\TH:i', $ban_end);
    if (!$ban_end_date || $ban_end_date->format('Y') > 9999) {
        $error_message = 'Không được phép cấm quá năm 9999';
    } elseif ($username === 'admin') {
        $error_message = 'Bạn không thể cấm tài khoản quản trị';
    } else {
        // Tiếp tục xử lý như trước
        $stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
        $stmt->bind_param("ss", $username, $ip_address);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = 'Người dùng hoặc IP đã bị cấm trước đó!';
        } else {
            if (!empty($username) || !empty($ip_address)) {
                if (!empty($username)) {
                    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $user = $result->fetch_assoc();
                        $user_id = $user['id'];

                        // Thêm vào danh sách cấm
                        $stmt = $conn->prepare("INSERT INTO bans (username, ip_address, reason, ban_start, ban_end, permanent) VALUES (?, ?, ?, NOW(), ?, ?)");
                        $stmt->bind_param("ssssi", $username, $ip_address, $reason, $ban_end_date->format('Y-m-d H:i:s'), $permanent);
                        $stmt->execute();

                        $success_message = "Đã cấm $username thành công.";
                        writeLog("Cấm người dùng: $username, Lý do: $reason, Đến: " . $ban_end_date->format('Y-m-d H:i:s') . ", IP: $ip_address");
                    } else {
                        $error_message = 'Không tìm thấy người dùng!';
                    }
                } elseif (!empty($ip_address)) {
                    $stmt = $conn->prepare("INSERT INTO bans (username, ip_address, reason, ban_start, ban_end, permanent) VALUES (?, ?, ?, NOW(), ?, ?)");
                    $username = 'IP_Censored';
                    $ban_end_formatted = $ban_end_date->format('Y-m-d H:i:s');  // Gán kết quả của format vào một biến
                    $stmt->bind_param("ssssi", $username, $ip_address, $reason, $ban_end_formatted, $permanent);                    
                    $stmt->execute();

                    $success_message = "Đã cấm IP $ip_address thành công.";
                    writeLog("Cấm IP: $ip_address, Lý do: $reason, Đến: " . $ban_end_date->format('Y-m-d H:i:s'));
                }
            } else {
                $error_message = 'Vui lòng nhập tên người dùng hoặc địa chỉ IP để cấm!';
            }
        }
    }
}
?>