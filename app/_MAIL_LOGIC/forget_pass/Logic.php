<?php
require '../app/vendor/autoload.php'; // Đảm bảo đã cài PHPMailer qua Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Kiểm tra nếu chưa qua bước captcha
if (!isset($_SESSION['captcha_verified'])) {
    header("Location: /src/captcha_verification.php");
    exit();
}

// Nếu chưa set bước thì khởi tạo bước 1
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1;
}

// Lấy cấu hình SMTP từ bảng misc (id chỉ có 1)
$stmtSMTP = $conn->prepare("SELECT account_smtp, password_smtp FROM misc WHERE id = 1");
$stmtSMTP->execute();
$resultSMTP = $stmtSMTP->get_result();
if ($resultSMTP->num_rows > 0) {
    $smtpData = $resultSMTP->fetch_assoc();
} else {
    die("Không tìm thấy cấu hình SMTP trong CSDL.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Bước 1: Nhập Gmail và gửi OTP
    if ($_SESSION['step'] == 1) {
        $gmail = isset($_POST['gmail']) ? trim($_POST['gmail']) : "";
        if (!empty($gmail)) {
            $stmt = $conn->prepare("SELECT username, password, is_active FROM users WHERE gmail = ?");
            $stmt->bind_param("s", $gmail);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($user['is_active'] != 1) {
                    $error = "Tài khoản của bạn chưa được kích hoạt. Vui lòng kích hoạt tài khoản.";
                } else {
                    $_SESSION['old_password_hash'] = $user['password'];
                    $_SESSION['otp'] = strtoupper(bin2hex(random_bytes(8)));
                    $_SESSION['reset_email'] = $gmail;
                    $_SESSION['otp_expiry']  = time() + 300;
                    
                    $subject = "Mã OTP của bạn";
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
                                Mã này có hiệu lực trong 5 phút kể từ khi nhận được. Hãy chắc chắn nhập đúng mã và trong thời gian quy định để hoàn tất quá trình xác thực.
                            </p>
                            <p style='font-size: 14px; color: #7d8a99;'>Lưu ý: Mã OTP là thông tin nhạy cảm, đừng chia sẻ với ai nhé.</p>
                            <div style='background-color: #e3f2fd; padding: 10px; border-radius: 8px; margin-top: 20px;'>
                                <h4 style='color: #1976d2;'>Hướng dẫn:</h4>
                                <ul style='list-style-type: none; padding: 0;'>
                                    <li style='font-size: 14px; color: #555;'>1. Nhập mã OTP vào trang xác nhận.</li>
                                    <li style='font-size: 14px; color: #555;'>2. Đảm bảo nhập chính xác mã.</li>
                                    <li style='font-size: 14px; color: #555;'>3. Nếu có vấn đề, thử lại hoặc yêu cầu OTP mới.</li>
                                </ul>
                            </div>
                            <p style='font-size: 12px; color: #999; margin-top: 30px;'>Nếu không yêu cầu OTP, bỏ qua email này nhé.</p>
                        </div>
                    </body>
                    </html>
                    ";

                    // Sử dụng PHPMailer để gửi mail OTP
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com'; // Thay đổi nếu dùng server khác
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $smtpData['account_smtp']; // Lấy từ CSDL
                        $mail->Password   = $smtpData['password_smtp'];  // Lấy từ CSDL
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;
                        
                        $mail->setFrom('no-reply@gmail.com', 'No Reply');
                        $mail->addAddress($gmail);
                        
                        $mail->isHTML(true);
                        $mail->CharSet = 'UTF-8';
                        $mail->Subject = $subject;
                        $mail->Body    = $message;
                        
                        $mail->send();
                        $_SESSION['step'] = 2;
                    } catch (Exception $e) {
                        $error = "Gửi mail OTP thất bại. Lỗi: " . $mail->ErrorInfo;
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
            unset($_SESSION['otp_attempts']);
            $_SESSION['step'] = 3;
        } else {
            if (!isset($_SESSION['otp_attempts'])) {
                $_SESSION['otp_attempts'] = 0;
            }
            $_SESSION['otp_attempts']++;
            $attempts_remaining = 10 - $_SESSION['otp_attempts'];
            if ($_SESSION['otp_attempts'] >= 10) {
                session_unset();
                session_destroy();
                echo "<div style='color: red; font-weight: bold; text-align: center; margin-top: 20px;'>
                        Bạn đã nhập sai OTP quá 10 lần. Hệ thống sẽ đóng sau 5 giây.
                      </div>";
                header("Refresh: 5; url=/");
                exit();
            } else {
                echo "<div style='color: red; font-weight: bold; text-align: center; margin-top: 20px;'>
                        Mã OTP không đúng. Còn {$attempts_remaining} lần thử.
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
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $gmail = $_SESSION['reset_email'];
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE gmail = ?");
            if (!$stmt) {
                $error = "Lỗi khi chuẩn bị câu lệnh SQL: " . $conn->error;
            } else {
                $stmt->bind_param("ss", $hashed_password, $gmail);
                if ($stmt->execute()) {
                    if ($conn->affected_rows > 0) {
                        $selectStmt = $conn->prepare("SELECT password FROM users WHERE gmail = ?");
                        $selectStmt->bind_param("s", $gmail);
                        $selectStmt->execute();
                        $result = $selectStmt->get_result();
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $new_db_hash = $row['password'];
                        } else {
                            $new_db_hash = "Không truy xuất được.";
                        }
                        $_SESSION['message'] = "Mật khẩu đã cập nhật thành công!<br>";
                        $_SESSION['step'] = 4;
                    } else {
                        $error = "Không có thay đổi nào. Có thể mật khẩu mới trùng với cũ.";
                    }
                } else {
                    $error = "Cập nhật mật khẩu thất bại: " . $stmt->error;
                }
            }
        }
    }
}
?>
