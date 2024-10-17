<?php
session_start();
include('config.php');

// Thiết lập múi giờ của PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];
$ip = $_SERVER['REMOTE_ADDR'];

// Đặt múi giờ cho MySQL
$conn->query("SET time_zone = '+07:00'");

$stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
$stmt->bind_param("ss", $username, $ip);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$ban = $result->fetch_assoc();
$reason = $ban['reason'];
$ban_end = $ban['ban_end'];

$now = new DateTime("now", new DateTimeZone('Asia/Ho_Chi_Minh'));
$ban_end_time = new DateTime($ban_end, new DateTimeZone('Asia/Ho_Chi_Minh'));

if ($ban_end_time < $now) {
    $ban_expired = true;
    $ban_end_display = '<strong style="color: red; font-weight: bold;">Đã hết hạn</strong>';
} else {
    $interval = $now->diff($ban_end_time);
    
    if ($interval->y >= 20) {
        $ban_end_display = '<strong style="color: red; font-weight: bold;">Vĩnh viễn</strong>';
    } elseif ($interval->d >= 7) {
        $ban_end_display = $ban_end_time->format('d/m/Y | H:i:s');
    } elseif ($interval->d > 0) {
        $ban_end_display = "Còn lại {$interval->d} ngày";
    } elseif ($interval->h > 0) {
        $ban_end_display = "Còn lại {$interval->h} giờ";
    } else {
        $ban_end_display = "Còn lại {$interval->i} phút";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agree_terms']) && $_POST['agree_terms'] === '1') {
    $stmt = $conn->prepare("DELETE FROM bans WHERE (username = ? OR ip_address = ?) AND ban_end IS NOT NULL AND ban_end < NOW()");
    $stmt->bind_param("ss", $username, $ip);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Lệnh cấm đã được xóa thành công.";
        $redirect_after = true;
    } else {
        $message = "Đã xảy ra lỗi. Có thể là do khác múi giờ, hãy thử lại sau 6-12 tiếng.";
        $redirect_after = false;
    }
} else {
    $message = "";
    $redirect_after = false;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấm Truy Cập</title>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script&display=swap" rel="stylesheet">
    <link rel="icon" href="favicon.png" type="image/png">
    <style>
/* Đảm bảo rằng tất cả các phần tử tính toán kích thước theo border-box */
*,
*::before,
*::after {
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    color: #343a40;
    margin: 0;
    padding: 0;
    text-align: center;
    overflow: hidden;
}

.container {
    max-width: 100%; /* Để khung không bị hẹp */
    width: 90%; /* Khung chiếm 90% chiều rộng của màn hình */
    max-width: 600px; /* Đặt chiều rộng tối đa */
    word-wrap: break-word; /* Đảm bảo chuỗi dài tự xuống dòng */
    margin: 80px auto;
    padding: 10px;
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

h1 {
    color: #dc3545;
    margin-bottom: 10px; /* Thu nhỏ khoảng cách dưới */
    font-size: 20px; /* Thu nhỏ kích thước tiêu đề */
}

p {
    font-size: 13px; /* Giảm font-size một chút */
    line-height: 1.5; /* Tăng khoảng cách giữa các dòng để dễ đọc */
    margin: 10px 0; /* Thêm khoảng cách trên và dưới đoạn văn */
    word-wrap: break-word; /* Tự động xuống dòng khi nội dung quá dài */
}

.warning {
    background-color: #fff3cd; /* Làm màu nền nhạt hơn để dễ nhìn */
    border: 1px solid #ffeeba; /* Giữ lại màu viền như cũ */
    padding: 10px; /* Tăng nhẹ padding để tạo khoảng cách bên trong */
    border-radius: 5px;
    margin-bottom: 15px; /* Tăng khoảng cách phía dưới giữa các phần tử */
    font-size: 13px; /* Đảm bảo kích thước chữ đồng nhất với đoạn văn */
    text-align: center; /* Canh trái nội dung trong khung cảnh báo */
}

.footer {
    margin-top: 15px;
    font-size: 10px; /* Thu nhỏ font-size của footer */
    color: #6c757d;
}

.checkbox-container {
    margin-top: 10px; /* Thu nhỏ margin */
}

.checkbox-container input[type="checkbox"] {
    margin-right: 5px; /* Thu nhỏ khoảng cách checkbox */
}

.agree-button {
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    padding: 6px 12px; /* Thu nhỏ padding */
    cursor: pointer;
    font-size: 12px; /* Thu nhỏ font-size */
    margin-top: 8px;
    opacity: 0.5;
    pointer-events: none;
}

.agree-button.active {
    opacity: 1;
    pointer-events: auto;
}

.alert {
    background-color: #dc3545;
    color: white;
    padding: 8px; /* Thu nhỏ padding */
    border-radius: 5px;
    margin-top: 10px;
}

.message {
    font-family: 'Dancing Script', cursive;
    font-size: 25px; /* Thu nhỏ font-size */
    font-weight: bold;
    margin: 10px 0;
}
.dino-game {
    margin-top: 20px; /* Tăng margin để dino không bị dính vào các phần khác */
    display: flex;
    justify-content: center;
    overflow: hidden; /* Ngăn cuộn */
    height: 400px; /* Đảm bảo chiều cao cố định */
}

.dino-game iframe {
    width: 100%; /* Đảm bảo game chiếm toàn bộ chiều rộng */
    height: 100%; /* Đảm bảo game chiếm toàn bộ chiều cao */
    border: none;
    border-radius: 10px;
}
</style>
</head>
<body>
    <div class="container">
        <h1>Cấm Truy Cập</h1>
        <div class="warning">
            <p>Bạn đã bị cấm truy cập vào trang web này vì vi phạm <a href="rules.html">Tiêu chuẩn cộng đồng</a>.</p>
            <p><strong>Lý do cấm:</strong> <?php echo htmlspecialchars($reason); ?></p>
            <p><strong>Thời gian cấm đến:</strong> <?php echo $ban_end_display; ?></p>
        </div>

        <?php if ($ban_expired): ?>
            <div class="checkbox-container">
                <label>
                    <input type="checkbox" id="agree_terms"> Bằng cách bấm vào nút này, bạn đồng ý với <strong> <a href="tos.html">Điều khoản dịch vụ </a> </strong> và chúng tôi sẽ cho bạn tái hòa nhập.
                </label>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="agree_terms" value="1">
                <button type="submit" id="agree_button" class="agree-button">Đồng ý</button>
            </form>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <div class="footer">
            <p>&copy; 2024 Bảo lưu mọi quyền.</p>
        </div>
    </div>

    <div class="message">
        Chờ đợi là hạnh phúc
    </div>

    <div class="dino-game">
        <iframe src="https://chromedino.com/" title="Khủng Long Game"></iframe>
    </div>

    <script>
        const checkbox = document.getElementById('agree_terms');
        const button = document.getElementById('agree_button');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                button.classList.add('active');
                button.disabled = false;
            } else {
                button.classList.remove('active');
                button.disabled = true;
            }
        });

        <?php if ($redirect_after): ?>
            // Nếu xóa thành công, chuyển hướng sau 3 giây
            setTimeout(function() {
                window.location.href = "index.php";
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>