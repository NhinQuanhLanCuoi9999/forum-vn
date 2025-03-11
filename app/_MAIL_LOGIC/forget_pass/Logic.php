<?php

// Kiểm tra nếu không có session 'captcha_verification'
if (!isset($_SESSION['captcha_verified'])) {
    // Chuyển hướng tới trang captcha_verification.php
    header("Location: /src/captcha_verification.php");
    exit(); // Đảm bảo không có mã nào được thực thi sau khi chuyển hướng
}

// Nếu chưa set bước thì khởi tạo bước 1
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Bước 1: Nhập Gmail và gửi OTP
    if ($_SESSION['step'] == 1) {
        $gmail = isset($_POST['gmail']) ? trim($_POST['gmail']) : "";
        if (!empty($gmail)) {
            // Cập nhật truy vấn để lấy thêm cột is_active
            $stmt = $conn->prepare("SELECT username, password, is_active FROM users WHERE gmail = ?");
            $stmt->bind_param("s", $gmail);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Lấy thông tin user để so sánh sau này (nếu cần)
                $user = $result->fetch_assoc();
                
                // Kiểm tra nếu is_active khác 1 thì thông báo và không gửi mail OTP
                if ($user['is_active'] != 1) {
                    $error = "Tài khoản của bạn chưa được kích hoạt. Vui lòng kích hoạt tài khoản.";
                } else {
                    $_SESSION['old_password_hash'] = $user['password'];
                    $_SESSION['otp'] = strtoupper(bin2hex(random_bytes(8)));
                    $_SESSION['reset_email'] = $gmail;
                    $_SESSION['otp_expiry']  = time() + 300;
                    
                    // Gửi mail OTP
                    $subject = "Mã OTP của bạn";

                    // Nội dung email với HTML
                    $message = "
                    <html>
                    <head>
                        <meta charset='UTF-8'>
                        <title>Xác nhận OTP của bạn</title>
                    </head>
                    <body style='background-color: #f4f7fc; font-family: Arial, sans-serif; padding: 20px; text-align: center;'>
                        <div style='background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); padding: 20px;'>
                            <h2 style='color: #4caf50; font-size: 24px;'>Xác nhận OTP của bạn</h2>
                            <p style='background-color: #f2f9ff; border: 1px solid #d1e7fd; border-radius: 8px; padding: 15px; color: #3a6ea5; font-size: 16px; line-height: 1.6;'>
                                Mã OTP của bạn là: <strong>" . $_SESSION['otp'] . "</strong><br>
                                Mã này có hiệu lực trong 5 phút kể từ khi nhận được. Hãy chắc chắn nhập mã đúng và trong thời gian quy định để hoàn tất quá trình xác thực.
                            </p>
                            <p style='font-size: 14px; color: #7d8a99;'>Lưu ý: Mã OTP là thông tin nhạy cảm, vui lòng không chia sẻ mã này với bất kỳ ai.</p>
                            <div style='background-color: #e3f2fd; padding: 10px; border-radius: 8px; margin-top: 20px;'>
                                <h4 style='color: #1976d2;'>Hướng dẫn sử dụng:</h4>
                                <ul style='list-style-type: none; padding: 0;'>
                                    <li style='font-size: 14px; color: #555;'>1. Nhập mã OTP vào trang xác nhận của bạn.</li>
                                    <li style='font-size: 14px; color: #555;'>2. Đảm bảo rằng bạn đã nhập đúng mã và không bị sai sót.</li>
                                    <li style='font-size: 14px; color: #555;'>3. Nếu bạn gặp vấn đề, vui lòng thử lại hoặc yêu cầu mã mới.</li>
                                </ul>
                            </div>
                            <p style='font-size: 12px; color: #999; margin-top: 30px;'>Nếu bạn không yêu cầu mã OTP này, vui lòng bỏ qua thông báo này và đảm bảo tài khoản của bạn được bảo mật.</p>
                        </div>
                    </body>
                    </html>
                    ";

                    // Cấu hình headers để gửi email dưới định dạng HTML
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";  // Đảm bảo định dạng HTML
                    $headers .= "From: no-reply@gmail.com" . "\r\n";  // Địa chỉ gửi email
                    
                    if(mail($gmail, $subject, $message, $headers)){
                        $_SESSION['step'] = 2;
                    } else {
                        $error = "Gửi mail OTP thất bại. Vui lòng kiểm tra cấu hình mail.";
                    }
                }
            } else {
                $error = "Gmail không tồn tại.";
            }
        } else {
            $error = "Vui lòng nhập Gmail.";
        }
    }
    // Bước 2: Nhập OTP
    elseif ($_SESSION['step'] == 2) {
        $input_otp = isset($_POST['otp']) ? trim($_POST['otp']) : "";

        if (empty($input_otp)) {
            $error = "Vui lòng nhập mã OTP.";
        } elseif (!isset($_SESSION['otp']) || time() > $_SESSION['otp_expiry']) {
            $error = "Mã OTP đã hết hạn hoặc không tồn tại.";
        } elseif ($input_otp == $_SESSION['otp']) {
            // Nếu OTP đúng, reset biến đếm số lần nhập sai (nếu có) và chuyển sang bước tiếp theo
            unset($_SESSION['otp_attempts']);
            $_SESSION['step'] = 3;
        } else {
            // Nếu OTP không đúng, tăng biến đếm số lần nhập sai
            if (!isset($_SESSION['otp_attempts'])) {
                $_SESSION['otp_attempts'] = 0;
            }
            $_SESSION['otp_attempts']++;

            // Tính số lần thử còn lại (cho đến 10 lần)
            $attempts_remaining = 10 - $_SESSION['otp_attempts'];

            if ($_SESSION['otp_attempts'] >= 10) {
                // Nếu nhập sai quá 10 lần, xóa hết session và thông báo
                session_unset();
                session_destroy();

                echo "<div style='color: red; font-weight: bold; text-align: center; margin-top: 20px;'>
                        Bạn đã nhập mã OTP sai quá 10 lần. Hệ thống sẽ đóng sau 5 giây.
                      </div>";

                // Chuyển hướng sau 5 giây (đảm bảo không có output nào khác trước header)
                header("Refresh: 5; url=/");
                exit();
            } else {
                // In thông báo số lần thử còn lại với inline CSS
                echo "<div style='color: red; font-weight: bold; text-align: center; margin-top: 20px;'>
                        Mã OTP không đúng. Bạn còn {$attempts_remaining} lần thử trước khi hệ thống khóa OTP.
                      </div>";

                $error = "Mã OTP không đúng.";
            }
        }
    }
// Bước 3: Nhập mật khẩu mới và cập nhật CSDL
elseif ($_SESSION['step'] == 3) {
    $new_password     = isset($_POST['new_password']) ? $_POST['new_password'] : "";
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : "";

    if (empty($new_password) || empty($confirm_password)) {
        $error = "Vui lòng nhập đầy đủ mật khẩu.";
    } elseif (strlen($new_password) < 6 || strlen($confirm_password) < 6) {
        $error = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Mật khẩu nhập lại không khớp.";
    } else {
        // Tạo hash cho mật khẩu mới
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $gmail = $_SESSION['reset_email'];
        
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE gmail = ?");
        if (!$stmt) {
            $error = "Lỗi khi chuẩn bị câu lệnh SQL: " . $conn->error;
        } else {
            $stmt->bind_param("ss", $hashed_password, $gmail);
            if ($stmt->execute()) {
                // Debug: kiểm tra affected_rows
                if ($conn->affected_rows > 0) {
                    // Sau UPDATE, thực hiện SELECT để xác nhận giá trị mới
                    $selectStmt = $conn->prepare("SELECT password FROM users WHERE gmail = ?");
                    $selectStmt->bind_param("s", $gmail);
                    $selectStmt->execute();
                    $result = $selectStmt->get_result();
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        // Lưu hash mới vào biến debug (nếu cần)
                        $new_db_hash = $row['password'];
                    } else {
                        $new_db_hash = "Không truy xuất được.";
                    }
                    
                    $_SESSION['message'] = "Mật khẩu đã cập nhật thành công!<br>";
                    $_SESSION['step'] = 4;
                } else {
                    // Nếu không có dòng nào thay đổi, có thể là mật khẩu mới trùng với mật khẩu cũ
                    $error = "Không có thay đổi nào được cập nhật. Có thể mật khẩu mới trùng với mật khẩu cũ.";
                }
            } else {
                $error = "Cập nhật mật khẩu thất bại: " . $stmt->error;
            }
        }
    }
}

}
?>
