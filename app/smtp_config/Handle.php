<?php
// Kiểm tra nếu form đã được gửi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['account_smtp'], $_POST['password_smtp'])) {

        // Nhận dữ liệu từ form
        $smtp_account  = $_POST['account_smtp'];
        $smtp_password = $_POST['password_smtp'];

        // Thiết lập thông tin email
        $to      = $smtp_account;
        $subject = "Test SMTP";
        $message = "Đây là email kiểm tra cấu hình SMTP.Nếu bạn nhận được mail này , đồng nghĩa với việc web của bạn đã cấu hình SMTP thành công.";
        $headers = "From: $smtp_account\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        // Gửi email kiểm tra
        if (mail($to, $subject, $message, $headers)) {
            // Chỉ khi mail() trả về true (gửi thành công) mới cập nhật CSDL
            $stmt = $conn->prepare("UPDATE misc SET account_smtp=?, password_smtp=? WHERE id=1")
                or die("Chuẩn bị truy vấn thất bại: " . $conn->error);
            $stmt->bind_param("ss", $smtp_account, $smtp_password);

            if ($stmt->execute()) {echo "<div class='alert alert-success' role='alert'>Cập nhật cơ sở dữ liệu thành công.</div>";}
             else {echo "<div class='alert alert-danger' role='alert'>Lỗi cập nhật cơ sở dữ liệu: " . $stmt->error . "</div>";}
            $stmt->close();
        } else {
            // Lấy thông tin lỗi nếu có
            $error = error_get_last();
            echo "<div class='alert alert-danger' role='alert'>Không thể gửi email qua SMTP. Vui lòng kiểm tra lại thông tin cấu hình SMTP. " . (isset($error['message']) ? "Lỗi: " . $error['message'] : "") . "</div>";}
    }
}
?>