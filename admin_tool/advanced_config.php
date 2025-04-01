<?php
session_start();
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/advanced_config/Handle.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/advanced_config/OAuth2.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/admin/logicPHP/Auth.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/admin/logicPHP/Check2FA.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/admin/logicPHP/Auth.php';

$message = ""; // Biến chứa thông báo
?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cấu Hình SMTP & Google Oauth</title>
  <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
  <link href="/asset/css/Poppins.css" rel="stylesheet">
  <style>
    body { 
      font-family: 'Poppins', sans-serif; 
      background: linear-gradient(to right, #f0ffff, #d4f1f9); /* Màu Azure */
    }
  </style>
</head>
<body>
<div class="d-grid mt-3">
  <a href="/admin_tool/admin.php" class="btn btn-secondary">Quay lại Admin</a>
</div>

<div class="container mt-5">
  <?php echo $message; ?>
  
  <div class="row">
    <!-- Form SMTP ban đầu -->
    <div class="col-md-6 mb-4">
      <div class="card shadow">
        <div class="card-body">
          <h2 class="card-title text-center mb-4">Thay đổi cấu hình SMTP</h2>
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
              <input type="submit" name="submit_smtp" class="btn btn-primary" value="Gửi">
            </div>
          </form>
          <p class="text-muted text-center mt-3">Lưu ý: Chỉ Admin mới có quyền thay đổi cấu hình SMTP.</p>
        </div>
      </div>
    </div>

    <!-- Form Google OAuth -->
    <div class="col-md-6 mb-4">
      <div class="card shadow">
        <div class="card-body">
          <h2 class="card-title text-center mb-4">Cấu hình Google Oauth</h2>
          <form method="POST" action="">
            <div class="mb-3">
              <label for="google_client_id" class="form-label">Google Client ID:</label>
              <input type="text" class="form-control" id="google_client_id" name="google_client_id" value="<?php echo htmlspecialchars($google_client_id); ?>" required>
            </div>
            <div class="mb-3">
              <label for="google_client_secret" class="form-label">Google Client Secret:</label>
              <input type="text" class="form-control" id="google_client_secret" name="google_client_secret" value="<?php echo htmlspecialchars($google_client_secret); ?>" required>
            </div>
            <div class="d-grid">
              <input type="submit" name="submit_google" class="btn btn-primary" value="Gửi">
            </div>
          </form>
          <p class="text-muted text-center mt-3">Lưu ý: Chỉ Admin mới có quyền thay đổi cấu hình Google Oauth.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="/asset/js/Bootstrap.bundle.min.js"></script>
</body>
</html>
