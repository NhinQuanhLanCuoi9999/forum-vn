<?php
session_set_cookie_params(900);
session_start();

include '../config.php'; // file config.php cần chứa cấu hình kết nối CSDL
include '../app/verify/Auth.php';
include '../app/verify/Declare.php';
include '../app/verify/Finale_Verify.php';
include '../app/verify/Handle.php';



?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xác minh Gmail</title>
    <!-- Bootstrap CSS -->
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
    <style>
        body {background-color: #f8f9fa;}
        .verification-container {max-width: 600px;margin: 50px auto;}
        form {background: #fff;padding: 30px;border-radius: 10px;box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);}
    </style>
</head>
<body>
    <div class="container verification-container">
        <h2 class="text-center">Xác minh Gmail</h2>
        <?php
        if (isset($activation_status)) {
            echo "<div class='alert alert-info'>" . htmlspecialchars($activation_status) . "</div>";
        }
        if (isset($mail_status)) {
            echo "<div class='alert alert-info'>" . $mail_status . "</div>";
        }
        if (isset($_SESSION['username'])) {
            if (!empty($gmail)) {
                echo "<p>Email được đăng ký: <strong>" . htmlspecialchars($gmail) . "</strong></p>";
            } 
        } else {
            echo "<p>Không có thông tin người dùng.</p>";
        }
        ?>
        <?php if (!isset($_GET['code'])): ?>
        <form method="POST" action="">
            <div class="d-grid gap-2">
                <button type="submit" name="verify" class="btn btn-primary">Gửi email xác minh</button>
            </div>
        </form>
        <?php endif; ?>
    </div>

    
    
    <!-- Bootstrap JS Bundle (bao gồm Popper) -->
    <script src="/asset/js/Bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>