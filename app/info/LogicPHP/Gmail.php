<?php
$userId = $_SESSION['username'];

// Truy vấn thông tin người dùng từ bảng users
$query = "SELECT gmail, is_active FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {die("Chuẩn bị câu truy vấn thất bại");}

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
        die("Chuẩn bị câu truy vấn thất bại");
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
                die("Chuẩn bị câu truy vấn thất bại: ");
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


?>