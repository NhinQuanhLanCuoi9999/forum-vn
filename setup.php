<?php
session_start();

// Nếu file config đã tồn tại, chuyển hướng về index
if (file_exists('config.php')) {
    header("Location: index.php");
    exit;
}

// Tạo token CSRF nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header("Refresh: 0");
}

// Load autoload của Composer
require 'app/vendor/autoload.php';

// Nếu gửi form POST, xử lý cài đặt
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // File validate.php sẽ kiểm tra CSRF và validate input
    require 'app/_SETUP/validate.php';
    $data = getInstallationData($_POST);
    
    try {
        // Kết nối database, tạo db và chạy file SQL
        require 'app/_SETUP/db_setup.php';
        $conn = setupDatabase($data['host'], $data['user'], $data['pass'], $data['db']);
        setupSQL($conn, 'db.sql');

        // Tạo tài khoản admin và lưu thông tin vào bảng misc
        setupAdmin($conn, $data['admin_pass']);
        setupMisc($conn, $data);

        // Test gửi email cấu hình SMTP
        require 'app/_SETUP/test_email.php';
        testEmail($data['smtp_account'], $data['smtp_password']);

        // Ghi thông tin cấu hình vào file config.php
        require 'app/_SETUP/config_writer.php';
        writeConfigFile('config.php', $data);

        echo "<h3 class='alert alert-success fade-in'>Cấu hình thành công! Bạn có thể đăng nhập với tài khoản admin.</h3>";
        exit;
    } catch (Exception $e) {
        echo "<script>alert('Lỗi: " . addslashes($e->getMessage()) . "');</script>";
    }
}
?>



<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình CSDL</title>
    <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
    <style>
        .fade-in { animation: fadeIn 0.5s ease-out forwards; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    </style>
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg w-100 mw-600">
        <div class="card-body">
            <h3 class="text-center mb-4 fade-in">Cấu hình CSDL</h3>
            <form method="POST">
                <!-- Thêm token bảo vệ CSRF -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <!-- Bước 1: Cấu hình cơ sở dữ liệu -->
                <div class="step" id="step-1">
                    <div class="mb-3">
                        <label for="host" class="form-label">Host</label>
                        <input type="text" id="host" name="host" class="form-control" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label for="user" class="form-label">Tên đăng nhập</label>
                        <input type="text" id="user" name="user" class="form-control" value="root" required>
                    </div>
                    <div class="mb-3">
                        <label for="pass" class="form-label">Mật khẩu</label>
                        <input type="password" id="pass" name="pass" class="form-control">
                    </div>
                    <button type="button" class="btn btn-primary w-100 next-step">Tiếp theo</button>
                </div>

                <!-- Bước 2: Tạo tài khoản Admin -->
                <div class="step d-none" id="step-2">
                    <h5 class="text-center mb-3">Tạo tài khoản Admin</h5>
                    <div class="mb-3">
                        <label for="admin_pass" class="form-label">Mật khẩu Admin</label>
                        <input type="password" id="admin_pass" name="admin_pass" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary prev-step">Quay lại</button>
                        <button type="button" class="btn btn-primary next-step">Tiếp theo</button>
                    </div>
                </div>

                <!-- Bước 3: Cấu hình API -->
                <div class="step d-none" id="step-3">
                    <h5 class="text-center mb-3">Cấu hình API</h5>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="hcaptcha_api_key" class="form-label">Hcaptcha API Key</label>
                        <input type="text" id="hcaptcha_api_key" name="hcaptcha_api_key" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="hcaptcha_site_key" class="form-label">hCaptcha Site Key</label>
                        <input type="text" id="hcaptcha_site_key" name="hcaptcha_site_key" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="ipinfo_api_key" class="form-label">Ipinfo API Key</label>
                        <input type="text" id="ipinfo_api_key" name="ipinfo_api_key" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary prev-step">Quay lại</button>
                        <button type="button" class="btn btn-primary next-step">Tiếp theo</button>
                    </div>
                </div>

                <!-- Bước 4: Cấu hình SMTP -->
                <div class="step d-none" id="step-4">
                    <h5 class="text-center mb-3">Cấu hình SMTP</h5>
                    <div class="mb-3">
                        <label for="account_smtp" class="form-label">Tài khoản Gmail</label>
                        <input type="email" id="account_smtp" name="account_smtp" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_smtp" class="form-label">Mật khẩu SMTP</label>
                        <input type="text" id="password_smtp" name="password_smtp" class="form-control" required>
                    </div>
                    <div class="d-flex justify-content-between">
                        <button type="button" class="btn btn-secondary prev-step">Quay lại</button>
                        <button type="submit" class="btn btn-success">Hoàn tất</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="../asset/js/jquery.min.js"></script>
    <script src="../asset/js/Bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {let currentStep = 1;const totalSteps = $(".step").length;function showStep(step) {$(".step").addClass("d-none");$("#step-" + step).removeClass("d-none").addClass("fade-in");}
            $(".next-step").click(function() {if (currentStep < totalSteps) {currentStep++;showStep(currentStep);}});
            $(".prev-step").click(function() {if (currentStep > 1) {currentStep--;showStep(currentStep);}});showStep(currentStep);});
    </script>
</body>
</html>
