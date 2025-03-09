<?php
$userId = $_SESSION['username'];

// Truy vấn thông tin người dùng từ bảng users
$query = "SELECT gmail, is_active FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $query);
if ($stmt === false) {
    die("Chuẩn bị câu truy vấn thất bại");
}

mysqli_stmt_bind_param($stmt, "s", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Kiểm tra nếu có thông tin người dùng
if ($user = mysqli_fetch_assoc($result)) {
    $currentGmail = $user['gmail'];
    $isActive = $user['is_active'];
}

// Giải phóng kết quả
mysqli_stmt_close($stmt);

// Hàm hiển thị thông báo đẹp
function showAlert($message, $type) {
    $icon = ($type === 'error') ? '❌' : '✅';
    $bgColor = ($type === 'error') ? 'rgba(255, 0, 0, 0.9)' : 'rgba(0, 128, 0, 0.9)';
    
    echo "<div style='
        position: fixed; top: 20px; right: 20px; z-index: 2000; width: 320px;
        padding: 15px; color: white; background-color: $bgColor; 
        border-radius: 8px; text-align: center; font-size: 16px; font-weight: bold;
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        opacity: 0; animation: fadeIn 0.5s forwards;'>
        $icon $message
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>";
}

// Xử lý khi người dùng gửi yêu cầu thay đổi Gmail
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gmail'])) {
    $newGmail = trim($_POST['gmail']);

    // Không cho phép gửi nếu Gmail rỗng
    if (empty($newGmail)) {
        showAlert("Gmail không được để trống!", "error");
    } else {
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
            showAlert("Gmail này đã tồn tại, vui lòng chọn Gmail khác!", "error");
        } else {
            // Cập nhật Gmail mới và reset is_active nếu Gmail mới khác với Gmail cũ
            if ($newGmail !== $currentGmail) {
                $updateQuery = "UPDATE users SET gmail = ?, is_active = '0' WHERE username = ?";
                $stmt = mysqli_prepare($conn, $updateQuery);
                if ($stmt === false) {
                    die("Chuẩn bị câu truy vấn thất bại");
                }

                mysqli_stmt_bind_param($stmt, "ss", $newGmail, $userId);

                // Thực thi câu lệnh và kiểm tra kết quả
                if (mysqli_stmt_execute($stmt)) {
                    // Cập nhật lại thông tin người dùng
                    $currentGmail = $newGmail;
                    $isActive = '0'; // Đặt trạng thái chưa kích hoạt
                    showAlert("Gmail đã được cập nhật thành công.", "success");
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>
