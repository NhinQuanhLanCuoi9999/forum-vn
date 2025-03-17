<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function testEmail($smtp_account, $smtp_password) {
    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_account;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom($smtp_account, 'Mail Server');
        // Gửi test email đến tài khoản SMTP đã cấu hình
        $mail->addAddress($smtp_account);
        $mail->isHTML(true);
        $mail->Subject = "Test Email";
        $mail->Body    = "<p>Đây là email test từ hệ thống cài đặt.</p>";
        $mail->AltBody = "Chúc mừng, ứng dụng Forum của bạn đã được cài đặt thành công.";
        $mail->send();
    } catch (Exception $mailEx) {
        throw new Exception("Không thể gửi email test: " . $mailEx->getMessage());
    }
}
?>
