<?php
session_start(); // Bắt đầu phiên
include '../config.php';
include '../app/_USERS_LOGIC/info/php.php';
include('../app/_USERS_LOGIC/profile/TimeFormat.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');
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
However, if you redistribute the source code, you must retain this license.
*/
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS -->
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
    <link href="/app/_USERS_LOGIC/info/styles.css" rel="stylesheet">
    <!-- Font Poppins -->
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <script src="/app/_USERS_LOGIC/info/gmail_and_desc.js"></script>
    <title>Thông tin tài khoản</title>
</head>
<body>
  <div class="container my-5">
    <div class="custom-card">
      <h1 class="card-title mb-4">THÔNG TIN TÀI KHOẢN</h1>
      
      <!-- Tên người dùng -->
      <div class="info-group row">
        <div class="col-6">Tên người dùng:</div>
        <div class="col-6 text-end">
          <strong><?php echo htmlspecialchars($username); ?></strong>
        </div>
      </div>
      <hr>

      <!-- ID -->
      <div class="info-group row">
        <div class="col-6">ID:</div>
        <div class="col-6 text-end">
          <strong><?php echo $row['id']; ?></strong>
        </div>
      </div>
      <hr>

      <!-- Ngày tạo -->
      <div class="info-group row">
        <div class="col-6">Ngày tạo:</div>
        <div class="col-6 text-end">
          <strong><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($createdAt))); ?></strong>
        </div>
      </div>
      <hr>

      <!-- Vai trò -->
      <div class="info-group row">
        <div class="col-6">Vai trò:</div>
        <div class="col-6 text-end">
          <strong><?php echo htmlspecialchars($userRole); ?></strong>
        </div>
      </div>
      <hr>

      <!-- IPv4 -->
      <div class="info-group row">
        <div class="col-6">IPv4:</div>
        <div class="col-6 text-end">
          <strong>
            <?php
              $ip = $_SERVER['REMOTE_ADDR'];
              echo htmlspecialchars($ip);
            ?>
          </strong>
        </div>
      </div>
      <hr>

      <!-- IPv6 -->
      <div class="info-group row">
        <div class="col-6">IPv6:</div>
        <div class="col-6 text-end">
          <strong><?php echo htmlspecialchars($ipv6); ?></strong>
        </div>
      </div>
      <hr>

      <!-- User Agent -->
      <div class="info-group row">
        <div class="col-6">User Agent:</div>
        <div class="col-6 text-end">
          <strong>
            <?php
              $agent = $_SERVER['HTTP_USER_AGENT'];
              echo htmlspecialchars($agent);
            ?>
          </strong>
        </div>
      </div>
      <hr>

      <!-- Mô tả bản thân -->
      <div class="info-group row align-items-center">
        <div class="col-6">Mô tả bản thân:</div>
        <div class="col-6 text-end">
          <strong><?php echo htmlspecialchars($userDesc ?: 'Chưa có mô tả.'); ?></strong>
          <button class="btn btn-primary ms-2" onclick="toggleDescForm()">Cập nhật mô tả</button>
        </div>
      </div>
      <hr>

      <!-- Gmail hiện tại -->
      <div class="info-group row align-items-center">
        <div class="col-6">Gmail hiện tại:</div>
        <div class="col-6 text-end">
          <strong id="gmailText"><?php echo htmlspecialchars($currentGmail ?: 'Chưa có Gmail'); ?></strong>
          <!-- Ẩn nút "Chỉnh sửa" nếu có lỗi để hiển thị form luôn -->
          <button id="editGmailBtn" class="btn btn-primary ms-2" <?php echo isset($_SESSION['error']) ? 'style="display:none;"' : ''; ?>>Chỉnh sửa</button>
        </div>
      </div>

      <!-- Trạng thái Gmail -->
      <div class="info-group row">
        <div class="col-6">Trạng thái Gmail:</div>
        <div class="col-6 text-end">
          <strong><?php echo $isActive == '1' ? 'Đã kích hoạt' : 'Chưa kích hoạt'; ?></strong>
        </div>
      </div>

      <!-- Form sửa Gmail (bao gồm thông báo lỗi) -->
      <div class="row mt-2" id="gmailForm" <?php echo isset($_SESSION['error']) ? 'style="display: block;"' : 'style="display: none;"'; ?>>
        <div class="col-12">
          <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
              <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
              ?>
            </div>
          <?php endif; ?>
          <form method="POST" action="">
            <div class="mb-2">
              <label for="gmail" class="form-label">Cập nhật Gmail:</label>
              <input type="email" class="form-control" id="gmail" name="gmail" 
                     value="<?php echo htmlspecialchars($currentGmail ?: ''); ?>" required>
            </div>
            <div>
              <button type="submit" class="btn btn-success">Lưu</button>
              <button type="button" id="cancelEdit" class="btn btn-secondary">Hủy</button>
            </div>
          </form>
        </div>
      </div>
      <hr>

      <!-- Form sửa mô tả -->
      <div class="row mt-3" id="update-desc-form" style="display: none;">
        <div class="col-12">
          <form method="POST" action="">
            <div class="mb-2">
              <label for="desc" class="form-label">Cập nhật mô tả bản thân:</label>
              <textarea id="desc" name="desc" rows="4" class="form-control"
                        placeholder="Nhập mô tả của bạn..." maxlength="255"><?php echo htmlspecialchars($userDesc); ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Lưu thay đổi</button>
          </form>
        </div>
      </div>
      <hr>

    <!-- Kích hoạt 2FA -->
<div class="info-group row align-items-center">
  <div class="col-6">
    <form method="post">
      <div class="d-flex align-items-center" style="gap: 10px;">
        <label for="switch2fa" style="margin-bottom: 0;">Kích hoạt 2FA:</label>
        <div class="form-check form-switch" style="margin-bottom: 0;">
          <input class="form-check-input" type="checkbox" id="switch2fa"
                 name="switch2fa" value="1" onchange="this.form.submit()"
                 <?php echo ($user['2fa'] == 1 ? 'checked' : ''); ?>>
        </div>
      </div>
    </form>
  </div>
</div>
<hr>


<!-- Trạng thái -->
<div class="info-group row">
    <div class="col-6">Lần cuối đăng nhập:</div>
    <div class="col-6 text-end">
        <strong>
            <?= !empty($lastLogin) ? formatTimeDiff($lastLogin) : 'Không xác định' ?>
        </strong>
    </div>
</div>
<hr>
      <!-- Đổi mật khẩu -->
      <div class="info-group row align-items-center">
        <div class="col-6">Đổi mật khẩu:</div>
        <div class="col-6 text-end">
          <a href="change_password.php" class="btn btn-danger">Click vào đây</a>
        </div>
      </div>
      <hr>

      <!-- Về trang chủ -->
      <div class="row">
        <div class="col text-center">
          <button class="btn btn-primary" onclick="window.location.href='index.php'">Trang chủ</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
