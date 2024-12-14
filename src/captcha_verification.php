<?php
include('../app/captcha/Auth.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Xác thực Captcha</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/app/captcha/styles.css">
</head>
<body>
    <div class="container">
        <div class="captcha-box">
            <h2>Xác thực bạn không phải là robot</h2>
            <p>Vui lòng hoàn thành captcha bên dưới để tiếp tục truy cập trang web.</p>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <form action="captcha_verification.php" method="POST">
                <div class="captcha-container">
                    <div class="h-captcha" data-sitekey="8ab08556-388c-4fca-b4e4-6844ec20b396"></div>
                </div>
                <button type="submit" class="btn-submit">Xác minh</button>
            </form>
        </div>
    </div>

    <!-- Nhúng script hCaptcha -->
    <script src="https://hcaptcha.com/1/api.js" async defer></script>
</body>
</html>