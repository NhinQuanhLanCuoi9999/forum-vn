<?php
session_start();
include '../config.php';
include '../app/forget_pass/Logic.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/app/forget_pass/style.css">
</head>
<body>
<div class="container">
    <h2>Quên mật khẩu</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['message']; 
                unset($_SESSION['message']); 
            ?>
        </div>
    <?php endif; ?>

    <?php if ($_SESSION['step'] == 1): ?>
        <!-- Form nhập Gmail -->
        <form action="" method="post">
            <div class="mb-3">
                <label for="gmail" class="form-label">Nhập Gmail</label>
                <input type="email" class="form-control" id="gmail" name="gmail" required>
            </div>
            <button type="submit" class="btn btn-primary">Gửi OTP</button>
        </form>

    <?php elseif ($_SESSION['step'] == 2): ?>
        <!-- Form nhập OTP -->
        <form action="" method="post">
            <div class="mb-3">
                <label for="otp" class="form-label">Nhập mã OTP</label>
                <input type="text" class="form-control" id="otp" name="otp" required>
            </div>
            <button type="submit" class="btn btn-primary">Xác thực OTP</button>
        </form>

    <?php elseif ($_SESSION['step'] == 3): ?>
        <!-- Form nhập mật khẩu mới -->
        <form action="" method="post">
        <div class="mb-3">
    <label for="new_password" class="form-label">Mật khẩu mới</label>
    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
</div>
<div class="mb-3">
    <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
</div>

            <button type="submit" class="btn btn-primary">Đặt lại mật khẩu</button>
        </form>

    <?php elseif ($_SESSION['step'] == 4): ?>
        <!-- Thông báo thành công -->
        <div class="alert alert-success">Mật khẩu đã cập nhật thành công!</div>

        <?php
        // Xóa mọi session ngoại trừ captcha_verification
        foreach ($_SESSION as $key => $value) {
            if ($key !== 'captcha_verification') {
                unset($_SESSION[$key]);
            }
        }

        // Chuyển hướng đến trang index sau 2 giây
        header("refresh:2; url=index.php");
        exit();
        ?>

    <?php endif; ?>
    
</div>
</body>
</html>
