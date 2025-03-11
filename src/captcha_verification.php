<?php
include('../app/_USERS_LOGIC/captcha/Auth.php');


/*
##############################################################
#                                                            #
# This is the LICENSE file of Forum VN                       #
# Copyright belongs to Forum VN, Original Author:            #
# NhinQuanhLanCuoi9999                                       #
#                                                            #
##############################################################

Copyright © 2025 Forum VN  
Original Author: NhinQuanhLanCuoi9999  
License: GNU General Public License v3.0  

You are free to use, modify, and distribute this software under the terms of the GPL v3.  
However, if you redistribute the source code, you must retain this license.  */


?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Xác thực Captcha</title>
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" type="text/css" href="/app/_USERS_LOGIC/captcha/styles.css">
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
                <div class="h-captcha" data-sitekey="<?php echo htmlspecialchars($sitekey); ?>"></div>
                </div>
                <button type="submit" class="btn-submit">Xác minh</button>
            </form>
        </div>
    </div>

    <!-- Nhúng script hCaptcha -->
    <script src="https://hcaptcha.com/1/api.js" async defer></script>
</body>
</html>