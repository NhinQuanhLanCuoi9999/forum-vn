<?php
session_start();
include('config.php');

// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$log_file = 'logs/ban-log.txt';

// Kiểm tra xem hàm writeLog đã được định nghĩa chưa
if (!function_exists('writeLog')) {
    function writeLog($message) {
        global $log_file;
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }
}

$error_message = '';
$success_message = ''; // Khởi tạo thông báo thành công

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

if (isset($_GET['unban'])) {
    $ban_id = $_GET['unban'];
    $stmt = $conn->prepare("DELETE FROM bans WHERE id = ?");
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();

    $success_message = "Đã hủy cấm thành công.";
    writeLog("Hủy cấm ID: $ban_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$bans = $conn->query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.username = users.username");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Quản lý lệnh cấm</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #dc3545;
        }
        h2 {
            color: #495057;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .ban-list {
            max-height: 900px;
            overflow-y: auto;
            margin-top: 20px;
            border-top: 1px solid #ced4da;
            padding-top: 10px;
        }
        .ban-item {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }
        .ban-item:last-child {
            border-bottom: none;
        }
        .unban-link {
            color: #dc3545;
            text-decoration: none;
        }
        .unban-link:hover {
            text-decoration: underline;
        }
        .error-message, .success-message {
            font-weight: bold;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .info-message {
            color: gray;
            font-style: italic;
        }
        .redirect-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            transform: translate(100px, 100px);
        }
        .redirect-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <button class="redirect-button" onclick="window.location.href='admin.php'">Quay lại</button>
    <div class="container">
        <h1>Quản lý lệnh cấm</h1>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirmBan();">
            <input type="text" name="username" placeholder="Tên người dùng (có thể để trống)">
            <input type="text" name="ip_address" placeholder="IP muốn cấm">
            <textarea name="reason" placeholder="Lý do cấm" required></textarea>
            <input type="datetime-local" name="ban_end" required>
            <small style="color: #6c757d;">Chọn ngày và giờ kết thúc lệnh cấm</small>
            <input type="submit" name="ban" value="Cấm">
        </form>

        <h2>Danh sách người dùng bị cấm</h2>
        <div class="ban-list">
            <?php if ($bans->num_rows === 0): ?>
                <p class="info-message">Chưa có người dùng nào bị cấm.</p>
            <?php else: ?>
                <?php while ($ban = $bans->fetch_assoc()): ?>
                    <div class="ban-item">
                        <p>Người dùng: <?php echo htmlspecialchars($ban['username'] ?? 'Không xác định'); ?> - 
                        IP: <?php echo htmlspecialchars($ban['ip_address']); ?> - 
                        Lý do: <?php echo htmlspecialchars($ban['reason']); ?> - 
                        Đến: <?php echo htmlspecialchars($ban['ban_end']); ?> 
                        <a class="unban-link" href="ban.php?unban=<?php echo $ban['id']; ?>" onclick="return confirmUnban();">Hủy cấm</a></p>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function confirmBan() {
            return confirm("Bạn có chắc chắn muốn cấm người dùng hoặc IP này không?");
        }

        function confirmUnban() {
            return confirm("Bạn có chắc chắn muốn hủy cấm người dùng hoặc IP này không?");
        }
    </script>
</body>
</html>
