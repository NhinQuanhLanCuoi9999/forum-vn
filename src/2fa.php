<?php
session_start();
include '../config.php';
include '../app/_MAIL_LOGIC/2fa/Auth.php';
include '../app/_MAIL_LOGIC/2fa/Handle.php';




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
