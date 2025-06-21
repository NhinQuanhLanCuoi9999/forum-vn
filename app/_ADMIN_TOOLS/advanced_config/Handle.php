<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/EncryptAES.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['account_smtp'], $_POST['password_smtp'])) {
        $smtp_account  = $_POST['account_smtp'];
        $smtp_password = $_POST['password_smtp'];

        $mail = new PHPMailer(true);

        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_account;
            $mail->Password   = $smtp_password;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($smtp_account, 'Test SMTP');
            $mail->addAddress($smtp_account);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Test SMTP";
            $mail->Body    = "Đây là email kiểm tra cấu hình SMTP.<br>Nếu bạn nhận được mail này, đồng nghĩa với việc web của bạn đã cấu hình SMTP thành công.";

            if ($mail->send()) {
                // ✅ Mã hóa trước khi lưu vào DB
                $encrypted_account  = encryptDataAES($smtp_account);
                $encrypted_password = encryptDataAES($smtp_password);

                $stmt = $conn->prepare("UPDATE misc SET account_smtp=?, password_smtp=? WHERE id=1")
                    or die("Chuẩn bị truy vấn thất bại: " . $conn->error);
                $stmt->bind_param("ss", $encrypted_account, $encrypted_password);

                if ($stmt->execute()) {
                    echo "<div class='alert alert-success' role='alert'>Cập nhật cơ sở dữ liệu thành công.</div>";
                } else {
                    echo "<div class='alert alert-danger' role='alert'>Lỗi cập nhật cơ sở dữ liệu: " . $stmt->error . "</div>";
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger' role='alert'>Không thể gửi email qua SMTP. Lỗi: {$mail->ErrorInfo}</div>";
        }
    }
}
?>