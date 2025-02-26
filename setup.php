<?php
session_start();

// Kiểm tra nếu file config tồn tại và kết nối thành công
if (file_exists('config.php')) {
    header("Location: index.php");
    exit;
}

$db = 'forum_db';

// Nếu file config đã tồn tại thì kết nối cơ sở dữ liệu
if (file_exists('config.php')) {
    include 'config.php';
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("<h3>Kết nối thất bại: " . $conn->connect_error . "</h3>");
    }

    $result = $conn->query("SHOW DATABASES LIKE '$db'");
    if ($result->num_rows == 0) {
        if ($conn->query("CREATE DATABASE $db")) {
            echo "<h3>Cơ sở dữ liệu $db đã được tạo!</h3>";
        } else {
            die("<h3>Không thể tạo cơ sở dữ liệu: " . $conn->error . "</h3>");
        }
    }

    $conn->select_db($db);
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Kiểm tra nếu form cấu hình CSDL, API và SMTP đã được gửi
        if (isset($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['admin_pass'], 
                  $_POST['title'], $_POST['name'], $_POST['hcaptcha_api_key'], $_POST['ipinfo_api_key'],
                  $_POST['account_smtp'], $_POST['password_smtp'])) {
            
            // Nhận dữ liệu từ form
            $host           = $_POST['host'];
            $user           = $_POST['user'];
            $pass           = $_POST['pass'];
            $title          = $_POST['title'];
            $name           = $_POST['name'];
            $hcaptchaApiKey = $_POST['hcaptcha_api_key'];
            $ipinfoApiKey   = $_POST['ipinfo_api_key'];
            $smtp_account   = $_POST['account_smtp'];
            $smtp_password  = $_POST['password_smtp'];
            
            try {
                // Test cấu hình SMTP: dùng mail() để gửi email kiểm tra tới tài khoản Gmail vừa nhập
                $to      = $smtp_account;
                $subject = "Test SMTP";
                $message = "Đây là email kiểm tra cấu hình SMTP.";
                $headers = "From: $smtp_account\r\n";
                if (!mail($to, $subject, $message, $headers)) {
                    throw new Exception("Không thể gửi email qua SMTP. Vui lòng kiểm tra lại thông tin cấu hình SMTP.");
                }
                
                // Kết nối cơ sở dữ liệu
                $conn = new mysqli($host, $user, $pass);
                if ($conn->connect_error) {
                    throw new Exception("Kết nối thất bại: " . $conn->connect_error);
                }
                
                // Kiểm tra và tạo database nếu chưa có
                $result = $conn->query("SHOW DATABASES LIKE '$db'");
                if ($result->num_rows == 0) {
                    if (!$conn->query("CREATE DATABASE $db")) {
                        throw new Exception("Không thể tạo cơ sở dữ liệu: " . $conn->error);
                    }
                    echo "<h3 class='alert alert-success'>Cơ sở dữ liệu $db đã được tạo!</h3>";
                }
                
                $conn->select_db($db);
                
                // Thực thi file SQL để thiết lập cơ sở dữ liệu
                $sqlFile = 'db.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    if (!$conn->multi_query($sql)) {
                        throw new Exception("Lỗi khi chạy SQL: " . $conn->error);
                    }
                    
                    // Xử lý kết quả trả về của multi_query
                    do {
                        if ($result = $conn->store_result()) {
                            $result->free();
                        }
                    } while ($conn->more_results() && $conn->next_result());
                }
                
                // Tạo tài khoản admin
                $adminName = 'admin';
                $adminPass = password_hash($_POST['admin_pass'], PASSWORD_BCRYPT);
                
                // Xóa bản ghi có id = 1 nếu có
                $conn->query("DELETE FROM users WHERE username = 'admin'");

                
                // Thêm tài khoản admin mới
                $conn->query("INSERT INTO users (username, password, role) VALUES ('$adminName', '$adminPass', 'owner')");

                
                // Thêm thông tin vào bảng misc, bao gồm thông tin API và SMTP
                $stmt = $conn->prepare("INSERT INTO misc (title, name, hcaptcha_api_key, ipinfo_api_key, account_smtp, password_smtp) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $title, $name, $hcaptchaApiKey, $ipinfoApiKey, $smtp_account, $smtp_password);
                $stmt->execute();
                $stmt->close();
                
                // Lưu thông tin cấu hình vào file config.php
                $configContent = "<?php\n";
                $configContent .= "\$host = '$host';\n";
                $configContent .= "\$db = '$db';\n";
                $configContent .= "\$user = '$user';\n";
                $configContent .= "\$pass = '$pass';\n";
                $configContent .= "\$conn = new mysqli(\$host, \$user, \$pass, \$db);\n";
                $configContent .= "?>\n";
                file_put_contents('config.php', $configContent);
                
                echo "<h3 class='alert alert-success fade-in'>Cấu hình thành công! Bạn có thể đăng nhập với tài khoản admin.</h3>";
                exit;
            } catch (Exception $e) {
                echo "<script>alert('Lỗi: " . addslashes($e->getMessage()) . "');</script>";
            }
        }
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
        $(document).ready(function() {
            let currentStep = 1;
            const totalSteps = $(".step").length;

            function showStep(step) {
                $(".step").addClass("d-none");
                $("#step-" + step).removeClass("d-none").addClass("fade-in");
            }

            $(".next-step").click(function() {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            });

            $(".prev-step").click(function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            });

            showStep(currentStep);
        });
    </script>
</body>
</html>
