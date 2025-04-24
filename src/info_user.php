<?php
session_start();
include '../config.php';
include '../app/_USERS_LOGIC/info/php.php';
include('../app/_USERS_LOGIC/profile/TimeFormat.php');
date_default_timezone_set('Asia/Ho_Chi_Minh');

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrap CSS -->
  <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
  <!-- Font Poppins -->
  <link href="/asset/css/Poppins.css" rel="stylesheet">
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Thông tin tài khoản</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-[#fdfcfb] to-[#e2d1c3] min-h-screen py-10">
  <div class="container">
  <?php
  // Hiện thông báo thành công/thất bại 
if (isset($_SESSION['success'])) {showAlert($_SESSION['success'], 'success');unset($_SESSION['success']);}
if (isset($_SESSION['error'])) {showAlert($_SESSION['error'], 'error');unset($_SESSION['error']);}
?>
    <!-- Card 1: Thông tin cơ bản -->
    <div class="card mb-4 shadow-sm rounded-2xl border-0 bg-gradient-to-br from-white to-gray-100">
      <div class="card-header font-semibold bg-gradient-to-r from-blue-100 to-blue-200 text-gray-800 border-0">
        Thông tin cơ bản
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-6 text-gray-500">Tên người dùng</div>
          <div class="col-6 text-end font-bold"><?=htmlspecialchars($username)?></div>
        </div>
        <div class="row mb-3">
          <div class="col-6 text-gray-500">ID</div>
          <div class="col-6 text-end font-bold"><?=$row['id']?></div>
        </div>
        <div class="row mb-3">
          <div class="col-6 text-gray-500">Ngày tạo</div>
          <div class="col-6 text-end font-bold"><?=date('d-m-Y H:i:s', strtotime($createdAt))?></div>
        </div>
        <div class="row">
          <div class="col-6 text-gray-500">Vai trò</div>
          <div class="col-6 text-end font-bold"><?=htmlspecialchars($userRole)?></div>
        </div>
      </div>
    </div>

    <!-- Card 2: Mạng & Thiết bị -->
    <div class="card mb-4 shadow-sm rounded-2xl border-0 bg-gradient-to-br from-white to-gray-100">
      <div class="card-header font-semibold bg-gradient-to-r from-purple-100 to-pink-100 text-gray-800 border-0">
        Mạng & Thiết bị
      </div>
      <div class="card-body">
        <div class="row mb-3">
          <div class="col-6 text-gray-500">IPv4</div>
          <div class="col-6 text-end font-bold"><?=htmlspecialchars($_SERVER['REMOTE_ADDR'])?></div>
        </div>
        <div class="row mb-3">
          <div class="col-6 text-gray-500">IPv6</div>
          <div class="col-6 text-end font-bold"><?=htmlspecialchars($ipv6)?></div>
        </div>
        <div class="row">
          <div class="col-6 text-gray-500">User Agent</div>
          <div class="col-6 text-end font-bold"><?=htmlspecialchars($_SERVER['HTTP_USER_AGENT'])?></div>
        </div>
      </div>
    </div>

    <!-- Card 3: Email & Mô tả -->
    <div class="card mb-4 shadow-sm rounded-2xl border-0 bg-gradient-to-br from-white to-gray-100">
      <div class="card-header font-semibold bg-gradient-to-r from-green-100 to-teal-100 text-gray-800 border-0">
        Email & Mô tả
      </div>
      <div class="card-body">
        <div class="row align-items-center mb-3">
          <div class="col-6 text-gray-500">Gmail hiện tại</div>
          <div class="col-6 text-end">
            <strong id="gmailText"><?=htmlspecialchars($currentGmail ?: 'Chưa có Gmail')?></strong>
            <button id="editGmailBtn" class="btn btn-outline-primary btn-sm ms-2 rounded-full px-3"
              <?=isset($_SESSION['error'])?'style="display:none;"':''?>>Chỉnh sửa</button>
          </div>
        </div>
        <?php if(isset($_SESSION['error'])): ?>
          <div class="alert alert-danger"><?=htmlspecialchars($_SESSION['error']); unset($_SESSION['error']);?></div>
        <?php endif; ?>
        <div id="gmailForm" class="mb-4" style="display: <?=isset($_SESSION['error'])?'block':'none'?>;">
          <form method="POST" action="">
            <div class="input-group mb-2">
              <input type="email" name="gmail" class="form-control" placeholder="Nhập Gmail mới"
                     value="<?=htmlspecialchars($currentGmail)?>" required>
              <button class="btn btn-success rounded-full" type="submit">Lưu</button>
              <button type="button" id="cancelEdit" class="btn btn-secondary rounded-full">Hủy</button>
            </div>
          </form>
        </div>

        <div class="row align-items-center mb-3">
          <div class="col-6 text-gray-500">Mô tả bản thân</div>
          <div class="col-6 text-end">
            <strong><?=htmlspecialchars($userDesc ?: 'Chưa có mô tả.')?></strong>
            <button class="btn btn-outline-primary btn-sm ms-2 rounded-full px-3" onclick="toggleDescForm()">Cập nhật</button>
          </div>
        </div>
        <div id="update-desc-form" class="mb-3" style="display:none;">
          <form method="POST" action="">
            <div class="mb-2">
              <textarea name="desc" rows="3" class="form-control" maxlength="255"
                        placeholder="Nhập mô tả..."><?=htmlspecialchars($userDesc)?></textarea>
            </div>
            <button class="btn btn-success rounded-full" type="submit">Lưu thay đổi</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Card 4: Bảo mật -->
    <div class="card mb-4 shadow-sm rounded-2xl border-0 bg-gradient-to-br from-white to-gray-100">
      <div class="card-header font-semibold bg-gradient-to-r from-yellow-100 to-orange-100 text-gray-800 border-0">
        Bảo mật
      </div>
      <div class="card-body">
        <form method="post" class="mb-3">
          <div class="form-check form-switch d-flex justify-content-between align-items-center">
            <label class="form-check-label text-gray-500" for="switch2fa">Kích hoạt 2FA</label>
            <input class="form-check-input" type="checkbox" id="switch2fa" name="switch2fa" value="1"
                   onchange="this.form.submit()" <?=($user['2fa']==1?'checked':'')?> <?=(!$isVerified?'disabled':'')?>>
          </div>
          <?php if(!$isVerified): ?>
            <small class="text-danger">Xác minh tài khoản trước khi bật 2FA: <a href="verify.php">ở đây</a></small>
          <?php endif; ?>
        </form>

        <div class="row mb-3">
          <div class="col-6 text-gray-500">Lần cuối đăng nhập</div>
          <div class="col-6 text-end font-bold"><?=!empty($lastLogin)?formatTimeDiff($lastLogin):'Không xác định'?></div>
        </div>

        <div class="text-center">
  <a href="change_password.php" class="btn btn-danger rounded-full px-4">Đổi mật khẩu</a>
</div>

      </div>
    </div>

    <!-- Nút về trang chủ -->
    <div class="text-center">
      <button class="btn btn-primary rounded-full px-4" onclick="location.href='index.php'">Trang chủ</button>
    </div>
  </div>

  <!-- JS -->
  <script src="/asset/js/bootstrap.bundle.min.js"></script>
  <script src="/app/_USERS_LOGIC/info/gmail_and_desc.js"></script>
</body>
</html>
