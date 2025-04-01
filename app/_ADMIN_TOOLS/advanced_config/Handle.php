<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';


// Kiểm tra nếu form đã được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['account_smtp'], $_POST['password_smtp'])) {

        // Nhận dữ liệu từ form
        $smtp_account  = $_POST['account_smtp'];
        $smtp_password = $_POST['password_smtp'];

        // Tạo instance PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Cấu hình SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Thay bằng SMTP server của bạn
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_account;  // SMTP Username
            $mail->Password   = $smtp_password; // SMTP Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // TLS encryption
            $mail->Port       = 587; // Cổng SMTP (587 cho TLS, 465 cho SSL)

            // Thiết lập thông tin email
            $mail->setFrom($smtp_account, 'Test SMTP'); // Người gửi
            $mail->addAddress($smtp_account); // Người nhận
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = "Test SMTP";
            $mail->Body    = "Đây là email kiểm tra cấu hình SMTP.<br>Nếu bạn nhận được mail này, đồng nghĩa với việc web của bạn đã cấu hình SMTP thành công.";

            // Gửi email
            if ($mail->send()) {
                // Nếu gửi email thành công, cập nhật CSDL
                $stmt = $conn->prepare("UPDATE misc SET account_smtp=?, password_smtp=? WHERE id=1")
                    or die("Chuẩn bị truy vấn thất bại: " . $conn->error);
                $stmt->bind_param("ss", $smtp_account, $smtp_password);

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
