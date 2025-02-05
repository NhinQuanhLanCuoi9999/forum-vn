<?php
session_start(); // Bắt đầu phiên
include '../config.php';
include '../app/info/php.php';


$userId = $_SESSION['username'];

// Truy vấn thông tin người dùng từ bảng users
$query = "SELECT gmail, is_active FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {die("Chuẩn bị câu truy vấn thất bại: " . mysqli_error($conn));}

mysqli_stmt_bind_param($stmt, "s", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Kiểm tra nếu có thông tin người dùng
if ($user = mysqli_fetch_assoc($result)) {$currentGmail = $user['gmail'];$isActive = $user['is_active'];}

// Giải phóng kết quả
mysqli_stmt_close($stmt);

// Xử lý khi người dùng gửi yêu cầu thay đổi Gmail
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gmail'])) {
    $newGmail = $_POST['gmail'];

    // Kiểm tra xem Gmail mới đã tồn tại chưa
    $checkQuery = "SELECT COUNT(*) FROM users WHERE gmail = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    if ($stmt === false) {
        die("Chuẩn bị câu truy vấn thất bại: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $newGmail);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $gmailCount);
    mysqli_stmt_fetch($stmt);

    // Giải phóng kết quả
    mysqli_stmt_close($stmt);

    // Nếu Gmail đã tồn tại
    if ($gmailCount > 0) {
        echo "<div style='color: white; background-color: red; padding: 10px; font-size: 16px; border-radius: 5px;transform: translate(730px,-410px);z-index: 2000;'>Gmail này đã tồn tại, vui lòng chọn Gmail khác!</div>";
    } else {
        // Cập nhật Gmail mới và reset is_active nếu Gmail mới khác với Gmail cũ
        if ($newGmail !== $currentGmail) {
            $updateQuery = "UPDATE users SET gmail = ?, is_active = '0' WHERE username = ?";
            $stmt = mysqli_prepare($conn, $updateQuery);
            if ($stmt === false) {
                die("Chuẩn bị câu truy vấn thất bại: " . mysqli_error($conn));
            }

            // Sử dụng 'ss' vì cả gmail và username đều là chuỗi
            mysqli_stmt_bind_param($stmt, "ss", $newGmail, $userId);

            // Thực thi câu lệnh và kiểm tra kết quả
            if (mysqli_stmt_execute($stmt)) {
                // Cập nhật lại thông tin người dùng
                $currentGmail = $newGmail;
                $isActive = '0'; // Đặt trạng thái chưa kích hoạt
                echo "<div style='color: white; background-color: green; padding: 10px; font-size: 16px; border-radius: 5px;transform: translate(730px,-410px);z-index: 2000;'>Gmail đã được cập nhật thành công.</div>";
            }
            mysqli_stmt_close($stmt);
        }
    }
}


// Đóng kết nối
mysqli_close($conn);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> <!-- Cấm phóng to, thu nhỏ -->
    <title>Thông tin người dùng</title>
    <!-- Nhúng font Poppins -->
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" type="text/css" href="/app/info/styles.css">
    <script src="/app/info/desc_switch.js"></script>

</head>
<body>

<div class="container">
    <h1>THÔNG TIN TÀI KHOẢN</h1>
    <div class="user-info">
        <p><span>Tên người dùng:</span> <strong><?php echo htmlspecialchars($username); ?></strong></p>
        <div class="line"></div>
        <p><span>ID:</span> <strong><?php echo htmlspecialchars($userId); ?></strong></p>
        <div class="line"></div>
        <p><span>Ngày tạo:</span> <strong><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($createdAt))); ?></strong></p>
        <div class="line"></div>
        <p><span>IPv4:</span><strong><?php $ip = $_SERVER['REMOTE_ADDR']; echo htmlspecialchars($ip); ?></strong></p>
        <div class="line"></div>
        <p><span>IPv6:</span><strong><?php echo htmlspecialchars($ipv6); ?></strong></p>
        <div class="line"></div>
        <p><span>User Agent:</span><strong><?php $agent = $_SERVER['HTTP_USER_AGENT']; echo htmlspecialchars($agent); ?></strong></p>
        <div class="line"></div>
        <!-- Hiển thị mô tả bản thân và nút sửa mô tả bên cạnh -->
        <div class="desc-container">
            <p><span>Mô tả bản thân:</span> <strong><?php echo htmlspecialchars($userDesc ?: 'Chưa có mô tả.'); ?></strong></p>
            <button class="button" onclick="toggleDescForm()">Cập nhật mô tả</button>
        </div>
        <div class="line"></div>


<!-- Hiển thị thông tin Gmail và trạng thái kích hoạt -->
<p>
  <span>Gmail hiện tại:</span>
  <strong id="gmailText"><?php echo htmlspecialchars($currentGmail ?: 'Chưa có Gmail'); ?></strong>
  <button id="editGmailBtn" class="button">Chỉnh sửa</button>
</p>
<p>
  <span>Trạng thái Gmail:</span>
  <strong><?php echo $isActive == '1' ? 'Đã kích hoạt' : 'Chưa kích hoạt'; ?></strong>
</p>



<!-- Form sửa Gmail (ẩn mặc định) -->
<div id="gmailForm" style="display: none; margin-top: 10px;">
  <form method="POST" action="">
    <label for="gmail">Cập nhật Gmail:</label>
    <input type="email" id="gmail" name="gmail" value="<?php echo htmlspecialchars($currentGmail ?: ''); ?>" required>
    <button type="submit" class="button">Lưu</button>
    <button type="button" id="cancelEdit" class="button">Hủy</button>
  </form>
</div>


<script src="/app/info/gmail_switch.js"></script>


        <!-- Form sửa mô tả (ẩn mặc định) -->
        <form id="update-desc-form" method="POST" action="" style="display:none;">
            <label for="desc">Cập nhật mô tả bản thân:</label>
            <textarea id="desc" name="desc" rows="4" cols="50" placeholder="Nhập mô tả của bạn..." maxlength="255"><?php echo htmlspecialchars($userDesc); ?></textarea>
            <br>
            <button type="submit" class="button">Lưu thay đổi</button>
        </form>

        <div class="line"></div>

        <!-- Đổi mật khẩu -->
        <p>
            <span>Đổi mật khẩu:</span>
            <strong>
                <a href="change_password.php" class="btn-red">Click vào đây</a>
            </strong>
        </p>
        <div class="line"></div>
        <button class="button" onclick="window.location.href='index.php'">Trang chủ</button>
    </div>
</div>

</body>
</html>
