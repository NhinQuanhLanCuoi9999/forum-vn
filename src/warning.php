<?php
include('../app/_USERS_LOGIC/warning/Auth.php');
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
    <title>Cấm Truy Cập</title>
    <link rel="stylesheet" href="../asset/css/DancingScript.css">
    <link rel="icon" href="/favicon.png" type="image/png">
   <link rel="stylesheet" type="text/css" href="/app/_USERS_LOGIC/warning/styles.css">
</head>
<body>
    <div class="container">
        <h1>Cấm Truy Cập</h1>
        <div class="warning">
            <p>Bạn đã bị cấm truy cập vào trang web này vì vi phạm <a href="/docs/rules.html">Tiêu chuẩn cộng đồng</a>.</p>
            <p><strong>Lý do cấm:</strong> <?php echo htmlspecialchars($reason); ?></p>
            <p><strong>Thời gian cấm đến:</strong> <?php echo $ban_end_display; ?></p>
        </div>

        <?php if ($ban_expired): ?>
            <div class="checkbox-container">
                <label>
                    <input type="checkbox" id="agree_terms"> Bằng cách bấm vào nút này, bạn đồng ý với <strong> <a href="/dosc/tos.html">Điều khoản dịch vụ </a> </strong> và chúng tôi sẽ cho bạn tái hòa nhập.
                </label>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="agree_terms" value="1">
                <button type="submit" id="agree_button" class="agree-button">Đồng ý</button>
            </form>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <div class="footer">
    <p>&copy; <span id="year"></span> Bảo lưu mọi quyền.</p>
</div>

<script>
    const currentYear = new Date().getFullYear();
    document.getElementById('year').textContent = currentYear;
</script>

    </div>

    <div class="message">
        Chờ đợi là hạnh phúc
    </div>

    <div class="dino-game">
        <iframe src="https://chromedino.com/" title="Khủng Long Game"></iframe>
    </div>

    <script>
        const checkbox = document.getElementById('agree_terms');
        const button = document.getElementById('agree_button');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                button.classList.add('active');
                button.disabled = false;
            } else {
                button.classList.remove('active');
                button.disabled = true;
            }
        });

        <?php if ($redirect_after): ?>
            // Nếu xóa thành công, chuyển hướng sau 3 giây
            setTimeout(function() {
                window.location.href = "index.php";
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>