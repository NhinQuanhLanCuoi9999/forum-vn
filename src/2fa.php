<?php
session_start();
include '../config.php';
include '../app/2fa/Auth.php';
include '../app/2fa/Handle.php';

// --------------------
// Hàm gửi email OTP
// (Nhận thêm biến $misc làm tham số để sử dụng thông tin SMTP từ DB)
// --------------------
function sendOTP($toEmail, $otp, $misc) {
    $subject = "Mã OTP xác thực 2FA";
    $message = "
    <html>
    <body style='font-family: Arial, sans-serif; background-color: #f7f7f7; margin: 0; padding: 0;'>
        <div style='max-width: 600px; margin: 20px auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);'>
            <div style='text-align: center; padding: 10px; font-size: 18px; color: #3b5998;'>
                Xác thực tài khoản của bạn
            </div>
            <div style='font-size: 16px; color: #333333; line-height: 1.5;'>
                <p>Xin chào,</p>
                <p>Mã OTP của bạn là:</p>
                <div style='font-size: 24px; font-weight: bold; color: #e74c3c; padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd; border-radius: 5px; text-align: center; margin-top: 20px;'>
                    {$otp}
                </div>
                <p>Vui lòng sử dụng mã này để hoàn tất quá trình xác thực 2FA của bạn.</p>
                <p>Chúc bạn một ngày tuyệt vời!</p>
            </div>
            <div style='font-size: 12px; text-align: center; color: #aaaaaa; margin-top: 30px;'>
                <p>Bạn nhận được email này vì đã yêu cầu xác thực tài khoản. Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
            </div>
        </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $misc['account_smtp'] . "\r\n";
    $headers .= "Reply-To: " . $misc['account_smtp'] . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    return mail($toEmail, $subject, $message, $headers);
}



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
  <title>Xác thực 2FA</title>
  <!-- Thêm Bootstrap CSS -->
  <link href="/asset/css/Bootstrap2.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <!-- Sử dụng thẻ card của Bootstrap -->
        <div class="card shadow-sm">
          <div class="card-body">
            <?php
            if (isset($_SESSION['info'])) {
              echo "<div class='alert alert-info' role='alert'>{$_SESSION['info']}</div>";
              unset($_SESSION['info']);
            }
            if (isset($_SESSION['error'])) {
              echo "<div class='alert alert-danger' role='alert'>{$_SESSION['error']}</div>";
              unset($_SESSION['error']);
            }
            ?>

            <h2>XÁC MINH 2FA</h2>
            <form method="POST">
              <div class="form-group">
                <label for="otp_input">Nhập mã OTP</label>
                <input type="text" class="form-control" id="otp_input" name="otp_input" required>
              </div>
              <button type="submit" class="btn btn-primary btn-block">Xác thực OTP</button>
              <p>Bạn không muốn đăng nhập? <strong><a href="?logout">Nhấn vào đây</a></strong> để đăng xuất.</p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
