<?php
// Sử dụng PHPMailer từ document root
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify'])) {
    if (empty($gmail)) {
        die("Không tìm thấy địa chỉ email của người dùng.");
    }

    // Tạo mã xác minh 64 ký tự (sử dụng 32 byte -> 64 ký tự hex)
    try {
        $verification_code = bin2hex(random_bytes(32));
    } catch (Exception $e) {
        die("Không thể tạo mã xác minh: " . $e->getMessage());
    }

    // Lưu mã xác minh, email và thời gian vào session
    $_SESSION['verification_code'] = $verification_code;
    $_SESSION['user_gmail'] = $gmail;
    $_SESSION['verification_time'] = time();

    // Xây dựng URL xác minh dựa trên host hiện tại
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $current_path = strtok($_SERVER["REQUEST_URI"], '?');
    $active_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . $current_path . "?code=" . $verification_code;

    // Lấy thông tin SMTP từ bảng misc (giả sử có account_smtp và password_smtp)
    $misc_query = $conn->query("SELECT account_smtp, password_smtp FROM misc WHERE id = 1 LIMIT 1");
    if ($misc_query && $misc_query->num_rows > 0) {
        $misc = $misc_query->fetch_assoc();
        $smtp_from     = $misc['account_smtp'];
        $smtp_password = $misc['password_smtp'];
    } else {
        die("Không tìm thấy thông tin SMTP.");
    }

    // Chuẩn bị nội dung email xác minh (định dạng HTML)
    $to      = $gmail;
    $subject = "Xác minh email của bạn";
    $message = "
    <html>
    <head>
      <title>Xác minh email</title>
      <style>
        body{font-family:Arial,sans-serif;background-color:#f4f4f4;text-align:center;padding:20px}
        .container{background:white;padding:20px;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.1);max-width:500px;margin:auto}
        h1{color:#333}
        p{font-size:16px;color:#555}
        .button{display:inline-block;background-color:#007bff;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;margin-top:20px;font-size:18px}
        .button:hover{background-color:#0056b3}
        .footer{margin-top:20px;font-size:14px;color:#777}
      </style>
    </head>
    <body>
      <div class='container'>
        <h1>Xác minh Email của bạn</h1>
        <p>Xin chào,</p>
        <p>Vui lòng xác minh email của bạn bằng cách nhấn vào nút bên dưới:</p>
        <a class='button' href='$active_url'>Xác minh Email</a>
        <p>Nếu nút không hoạt động, bạn có thể sao chép và dán liên kết sau vào trình duyệt:</p>
        <p><a href='$active_url'>$active_url</a></p>
        <p class='footer'>Nếu bạn không yêu cầu điều này, hãy bỏ qua email này.</p>
      </div>
    </body>
    </html>
    ";

  // Khởi tạo PHPMailer và cấu hình gửi mail qua SMTP
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    // Cấu hình SMTP server (ví dụ: smtp.gmail.com)
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtp_from;        // Email người gửi
    $mail->Password   = $smtp_password;    // Mật khẩu SMTP hoặc App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Đảm bảo sử dụng UTF-8 để tránh lỗi font
    $mail->CharSet    = 'UTF-8';
    $mail->Encoding   = 'base64';

    // Cấu hình người gửi & người nhận
    $mail->setFrom($smtp_from, 'Mail Server');
    $mail->addAddress($to);

    // Cấu hình nội dung email
    $mail->isHTML(true);
    // Mã hóa tiêu đề email theo UTF-8 với base64
    $mail->Subject  = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $mail->Body     = $message;
    $mail->AltBody  = strip_tags($message);

    $mail->send();
    $mail_status = "Email xác minh đã được gửi tới <strong>" . htmlspecialchars($gmail) . "</strong>. Vui lòng kiểm tra hộp thư đến của bạn.";
} catch (Exception $e) {
    $mail_status = "Gửi email xác minh thất bại. Lỗi: {$mail->ErrorInfo}";
}
}
?>
