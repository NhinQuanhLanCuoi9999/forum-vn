<?php
session_start();
include '../config.php';

// Kiểm tra nếu không có session username hoặc username không phải là "admin"
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: /");
    exit(); 
}

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

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Cấu Hình SMTP</title>
  <!-- Bootstrap CSS -->
  <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts: Poppins -->
  <link href="/asset/css/Poppins.css" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>
<body class="bg-primary bg-gradient">
<div class="d-grid mt-3">
  <a href="/admin_tool/admin.php" class="btn btn-secondary"> Quay lại Admin</a>
</div>

  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow" style="max-width: 400px; width: 100%;">
      <div class="card-body">
        <h2 class="card-title text-center mb-4">Admin - Cấu Hình SMTP</h2>
        <form method="POST" action="">
          <div class="mb-3">
            <label for="account_smtp" class="form-label">Account SMTP:</label>
            <input type="email" class="form-control" id="account_smtp" name="account_smtp" required>
          </div>
          <div class="mb-3">
            <label for="password_smtp" class="form-label">Password SMTP:</label>
            <input type="password" class="form-control" id="password_smtp" name="password_smtp" required>
          </div>
          <div class="d-grid">
            <input type="submit" class="btn btn-primary" value="Gửi">
          </div>
        </form>
        <p class="text-muted text-center mt-3">Lưu ý: Chỉ Admin mới có quyền thay đổi cấu hình SMTP.</p>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS Bundle (bao gồm Popper) -->
  <script src="/asset/js/Bootstrap.bundle.min.js"></script>
</body>
</html>
