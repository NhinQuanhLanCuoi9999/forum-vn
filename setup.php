<?php
session_start();

// Kiểm tra nếu mật khẩu đã được nhập chính xác
if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
    // Kiểm tra xem có mật khẩu gửi đến hay không
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $entered_password = $_POST['password'];
        
        // Mật khẩu chính xác (thay thế với mật khẩu thực tế của bạn)
        $correct_password = '123456';
        
        if ($entered_password === $correct_password) {
            $_SESSION['authenticated'] = true;
            header("Location: " . $_SERVER['PHP_SELF']); // Làm mới trang sau khi xác thực thành công
            exit;
        } else {
            $error_message = "Mật khẩu sai, vui lòng thử lại.";
        }
    }
}

// Nếu đã xác thực, tiếp tục cấu hình CSDL
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
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
            // Kiểm tra nếu form cấu hình CSDL đã được gửi
            if (isset($_POST['host'], $_POST['user'], $_POST['pass'], $_POST['admin_pass'])) {
                // Nhận dữ liệu từ form
                $host = $_POST['host'];
                $user = $_POST['user'];
                $pass = $_POST['pass'];

                // Kết nối cơ sở dữ liệu
                $conn = new mysqli($host, $user, $pass);

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

                // Thực thi file SQL để thiết lập cơ sở dữ liệu
                $sqlFile = 'db.sql';
                if (file_exists($sqlFile)) {
                    $sql = file_get_contents($sqlFile);
                    if ($conn->multi_query($sql)) {
                        do {
                            // Xử lý từng câu lệnh SQL
                        } while ($conn->next_result());
                    }
                }

                // Tạo tài khoản admin
                $adminName = 'admin'; // Gán tự động tài khoản là "admin"
                $adminPass = password_hash($_POST['admin_pass'], PASSWORD_BCRYPT);

                // Xóa bản ghi có id = 1 nếu có
                $conn->query("DELETE FROM users WHERE id = 1");

                // Thêm tài khoản admin mới
                $conn->query("INSERT INTO users (id, username, password) VALUES (1, '$adminName', '$adminPass')");

                // Lưu thông tin cấu hình vào file config.php
                $configContent = "<?php\n";
                $configContent .= "\$host = '$host';\n";
                $configContent .= "\$db = '$db';\n";
                $configContent .= "\$user = '$user';\n";
                $configContent .= "\$pass = '$pass';\n";
                $configContent .= "\$conn = new mysqli(\$host, \$user, \$pass, \$db);\n";
                $configContent .= "?>\n";
                file_put_contents('config.php', $configContent);

                echo "<h3>Cấu hình thành công! Bạn có thể đăng nhập với tài khoản admin.</h3>";
                exit;
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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
        <div class="card shadow-lg" style="width: 100%; max-width: 600px;">
            <div class="card-body">
                <?php if (!isset($_SESSION['authenticated']) || !$_SESSION['authenticated']): ?>
                    <h3 class="text-center mb-4">Nhập Mật Khẩu Để Tiếp Tục</h3>
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Xác Nhận</button>
                    </form>
                <?php else: ?>
                    <h3 class="text-center mb-4">Cấu hình CSDL</h3>
                    <form method="POST">
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

                        <h5 class="text-center mb-3">Tạo tài khoản Admin</h5>

                        <div class="mb-3">
                            <label for="admin_pass" class="form-label">Mật khẩu Admin</label>
                            <input type="password" id="admin_pass" name="admin_pass" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Cấu hình</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS (cho các thành phần động như modal, tooltip) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
