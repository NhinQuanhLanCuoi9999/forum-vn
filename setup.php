<?php
session_start();

// Kiểm tra nếu file config tồn tại và kết nối thành công
if (file_exists('config.php')) {
    header("Location: index.php");
    exit;
}

$db = 'forum_db';

// Kiểm tra nếu file config tồn tại và kết nối thành công
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
        // Kiểm tra nếu form cấu hình CSDL và API đã được gửi
        if (isset($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['admin_pass'], 
                  $_POST['title'], $_POST['name'], $_POST['hcaptcha_api_key'], $_POST['ipinfo_api_key'])) {
            
            // Nhận dữ liệu từ form
            $host = $_POST['host'];
            $user = $_POST['user'];
            $pass = $_POST['pass'];
            $title = $_POST['title'];
            $name = $_POST['name'];
            $hcaptchaApiKey = $_POST['hcaptcha_api_key'];
            $ipinfoApiKey = $_POST['ipinfo_api_key'];

            try {
                // Kết nối cơ sở dữ liệu
                $conn = new mysqli($host, $user, $pass);

                if ($conn->connect_error) {
                    throw new Exception("Kết nối thất bại: " . $conn->connect_error);
                }

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
                $conn->query("DELETE FROM users WHERE id = 1");

                // Thêm tài khoản admin mới
                $conn->query("INSERT INTO users (id, username, password) VALUES (1, '$adminName', '$adminPass')");

                // Thêm thông tin vào bảng misc
                $stmt = $conn->prepare("INSERT INTO misc (title, name, hcaptcha_api_key, ipinfo_api_key) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $title, $name, $hcaptchaApiKey, $ipinfoApiKey);
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
                echo "<h3 class='alert alert-danger fade-in'>Lỗi: " . $e->getMessage() . "</h3>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình CSDL</title>
    <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
    <style>
        .fade-in {
            animation: fadeIn 1s ease-out forwards;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card shadow-lg" style="width: 100%; max-width: 600px;">
            <div class="card-body">
                <h3 class="text-center mb-4 fade-in">Cấu hình CSDL</h3>
                <form method="POST">
                    <!-- Cấu hình cơ sở dữ liệu -->
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

                    <!-- Nút xem thêm để mở rộng phần tạo tài khoản Admin -->
                    <button type="button" id="toggle-admin-config" class="btn btn-link w-100">Xem thêm</button>

                    <div id="admin-pass-section" class="d-none">
                        <h5 class="text-center mb-3">Tạo tài khoản Admin</h5>

                        <div class="mb-3">
                            <label for="admin_pass" class="form-label">Mật khẩu Admin</label>
                            <input type="password" id="admin_pass" name="admin_pass" class="form-control" required>
                        </div>

                        <!-- Cấu hình API -->
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

                        <button type="submit" class="btn btn-primary w-100">Cấu hình</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Thêm jQuery và Bootstrap JS -->
    <script src="../asset/js/jquery.min.js"></script>
    <script src="../asset/js/Bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#toggle-admin-config').on('click', function() {
                var adminPassSection = $('#admin-pass-section');
                var toggleButton = $('#toggle-admin-config');

                // Kiểm tra xem phần cấu hình Admin đang ẩn hay không
                if (adminPassSection.hasClass('d-none')) {
                    // Sử dụng slideDown để trượt phần cấu hình Admin
                    adminPassSection.removeClass('d-none').slideDown();
                    toggleButton.text('Ẩn đi');
                } else {
                    // Sử dụng slideUp để trượt ẩn phần cấu hình Admin
                    adminPassSection.slideUp(function() {
                        adminPassSection.addClass('d-none');
                    });
                    toggleButton.text('Xem thêm');
                }
            });
        });
    </script>
</body>
</html>
