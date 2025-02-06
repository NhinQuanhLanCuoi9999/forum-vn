<?php
session_start();
include '../config.php';
include '../app/smtp_config/Handle.php';
include '../app/smtp_config/Auth.php';
include '../app/smtp_config/Check2FA.php';

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cấu Hình SMTP</title>
  <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
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
