<?php
// Nếu có thông tin người dùng, lấy gmail từ DB
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $query = "SELECT gmail FROM users WHERE username = '$username'";
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $gmail = $row['gmail'];
    } else {
        $gmail = '';
    }

    // Nếu gmail trống thì kiểm tra submit form nhập gmail
    if (empty($gmail)) {
        if (isset($_POST['submit_gmail'])) {
            $input_gmail = trim($_POST['gmail']);
            if (filter_var($input_gmail, FILTER_VALIDATE_EMAIL)) {
                // Kiểm tra xem email đã tồn tại cho user khác chưa
                $check_query = "SELECT COUNT(*) AS count FROM users WHERE gmail = '$input_gmail' AND username != '$username'";
                $check_result = $conn->query($check_query);
                $check_row = $check_result->fetch_assoc();
                if ($check_row['count'] > 0) {
                    $error = "Email này đã được sử dụng. Vui lòng sử dụng email khác!";
                } else {
                    // Lưu gmail vào bảng users
                    $update_query = "UPDATE users SET gmail = '$input_gmail' WHERE username = '$username'";
                    if ($conn->query($update_query)) {
                        header("Refresh:0");
                        exit();
                    } else {
                        $error = "Lỗi khi lưu email. Thử lại nhé!";
                    }
                }
            } else {
                $error = "Email không hợp lệ. Vui lòng nhập lại.";
            }
        }
    }
}
?>