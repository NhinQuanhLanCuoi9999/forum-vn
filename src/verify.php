<?php
session_set_cookie_params(900);
session_start();

include '../config.php'; // file config.php cần chứa cấu hình kết nối CSDL
include '../app/_MAIL_LOGIC/verify/Auth.php';
include '../app/_MAIL_LOGIC/verify/Declare.php';
include '../app/_MAIL_LOGIC/verify/Finale_Verify.php';
include '../app/_MAIL_LOGIC/verify/Handle.php';
include '../app/_MAIL_LOGIC/verify/CheckGmail.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xác minh Gmail</title>
    <link href="/asset/css/Poppins.css" rel="stylesheet">
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
    <style>
        body {font-family: 'Poppins', sans-serif;}
        .verification-container {max-width: 600px;margin: 50px auto;}
    </style>
</head>
<body class="bg-light">
    <div class="container verification-container">
        <div class="card shadow-sm">
            <div class="card-header text-center bg-primary text-white">
                Xác minh Gmail
            </div>
            <div class="card-body">
                <?php
                if (isset($activation_status)) {
                    echo "<div class='alert alert-info'>" . htmlspecialchars($activation_status) . "</div>";
                }
                if (isset($mail_status)) {
                    echo "<div class='alert alert-info'>" . $mail_status . "</div>";
                }
                if (isset($_SESSION['username'])) {
                    // Nếu gmail trống, hiện form nhập gmail
                    if (empty($gmail)) {
                        if (isset($error)) {
                            echo "<div class='alert alert-danger'>" . htmlspecialchars($error) . "</div>";
                        }
                        ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="gmail" class="form-label">Nhập Gmail của bạn:</label>
                                <input type="email" name="gmail" id="gmail" class="form-control" placeholder="example@gmail.com" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="submit_gmail" class="btn btn-primary">Lưu Gmail</button>
                            </div>
                        </form>
                        <?php
                    } else {
                        ?>
                        <p class="mb-3">Email được đăng ký: <strong><?= htmlspecialchars($gmail) ?></strong></p>
                        <!-- Khung thông báo quyền sau khi kích hoạt -->
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                Thông Báo Quan Trọng
                            </div>
                            <div class="card-body">
                                <p class="card-text">
                                    - Sau khi xác minh tài khoản, bạn sẽ được cấp quyền bình luận và trả lời trên hệ thống.<br>
                                    - Đồng thời, tài khoản của bạn sẽ được kích hoạt sử dụng cơ chế xác thực 2FA nhằm bảo đảm an toàn tối đa cho thông tin cá nhân. <br>
                                    - Vui lòng hoàn thành quá trình xác minh qua email để trải nghiệm đầy đủ các tính năng bảo mật và tương tác của hệ thống.
                                </p>
                            </div>
                        </div>
                        <!-- Hiển thị nút gửi email xác minh nếu có gmail -->
                        <?php if (!isset($_GET['code'])): ?>
                            <form method="POST" action="">
                                <div class="d-grid">
                                    <button type="submit" name="verify" class="btn btn-primary">Gửi email xác minh</button>
                                </div>
                            </form>
                        <?php endif; ?>
                        <?php
                    }
                } else {
                    echo "<p>Không có thông tin người dùng.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
