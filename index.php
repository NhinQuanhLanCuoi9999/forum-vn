<?php
session_start(); // Đảm bảo session đã được bắt đầu
include('config.php'); // Bao gồm file config để kết nối DB
// Bao gồm tệp index.php sau khi kiểm tra cấm xong
include('app/index/php.php');
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" href="/asset/css/FontAwesome.min.css">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="icon" href="/favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="app/index/styles.css">

   <script src = app/index/Toogle.js></script>
    
  <script src = app/index/Refersh.js></script>
<script src = app/index/Spoil.js></script>
</head>
<body>
   <div id="mobile-warning">
        Vui lòng bật chế độ xem trên máy tính
    </div>

    <script src = app/index/Size.js></script>
<div class="container">
<h1 class="text-center mb-4 fade-in"><?php echo htmlspecialchars($forum_name); ?></h1>
    <?php if (!isset($_SESSION['username'])): ?>
        <!-- Hiển thị form nếu chưa đăng nhập -->
        <form id="login-form" method="post" action="index.php" style="display: block;">
        <?php
// Khởi tạo CSRF token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Tạo token ngẫu nhiên
}
?>

<form id="login-form" method="post" action="index.php" style="display: block;">
    <h2>Đăng nhập</h2>
    <input type="text" name="username" placeholder="Tên đăng nhập" required maxlength="50">
    <input type="password" name="password" placeholder="Mật khẩu" required>
    <a href="/src/forget_pass.php">Quên mật khẩu?</a> <br>
    <!-- Thêm CSRF token vào form -->
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    
    <button type="submit" name="login">Đăng nhập</button>
    <p>Chưa có tài khoản? <span class="toggle-link" style="color: red;" onclick="toggleForms()">Đăng ký</span></p>
</form>


        <form id="register-form" method="post" action="index.php" style="display: none;">
      <?php  // Tạo token CSRF nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Tạo token CSRF ngẫu nhiên
}
?>
<h2>Đăng ký</h2>
<form id="registrationForm" method="POST" action="register.php">
    <input type="text" name="username" placeholder="Tên đăng nhập" required pattern="^[a-zA-Z0-9]{5,30}$"
        title="Vui lòng chỉ nhập ký tự chữ và số không dấu và không có khoảng trắng hoặc ký tự đặc biệt. Nhập từ 5 đến 30 ký tự.">
    
    <input type="password" name="password" id="password" placeholder="Mật khẩu" required 
        minlength="6" maxlength="30" 
        pattern="^[a-zA-Z0-9]{6,30}$"
        title="Vui lòng chỉ nhập ký tự chữ và số, không có khoảng trắng hoặc ký tự đặc biệt. Nhập từ 6 đến 30 ký tự.">
    
    <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu" required>

    <input type="email" name="gmail" placeholder="Email" required>
    
    <!-- Token CSRF -->
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

    <!-- Checkbox và liên kết -->
    <label>
        <input type="checkbox" id="agreeCheckbox" onclick="toggleSubmitButton()"> 
        Bằng cách nhấn vào nút này, bạn đồng ý <a href="/docs/tos.html" target="_blank"><strong>Điều khoản dịch vụ</strong><b>.</b></a> <br>
    </label>
    
    <!-- Nút đăng ký mặc định xám và có hiệu ứng chuyển màu -->
    <button type="submit" name="register" id="registerBtn" disabled style="background-color: #9e9e9e;">Đăng ký</button>
    <p>Đã có tài khoản? <span class="toggle-link" style="color: red;" onclick="toggleForms()">Đăng nhập</span></p>
    
    <!-- Thanh tiến độ mật khẩu -->
    <div id="passwordStrengthContainer" style="display:none;">
        <progress id="passwordStrength" value="0" max="100"></progress>
        <span id="passwordStrengthText"></span>
    </div>
</form>

<script src="/app/index/ProgressBar.js"></script>
<script src = app/index/checkBox.js></script>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
        </form>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
    <?php else: ?>
      
        <!-- Hiển thị form đăng bài nếu đã đăng nhập -->
        <form action="index.php" method="POST" enctype="multipart/form-data">
    <h2>Đăng bài viết</h2>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <textarea name="content" placeholder="Nội dung bài viết" required maxlength="200"></textarea>
    <input type="text" name="description" placeholder="Mô tả ngắn" required maxlength="500">
    
    <!-- Trường tải lên tệp -->
    <label for="file">Chọn tệp để tải lên:</label>
    <input type="file" name="file" id="file">
     <!-- CSRF Token -->
     <input type="hidden" name="csrf_token2" value="<?php echo $_SESSION['csrf_token2']; ?>">
    <button type="submit" name="post">Đăng bài</button>
</form>

</head>
<body>

<button id="optionsBtn">Tùy chọn</button>

<div id="optionsMenu" class="dropdown-content">
    <a href="src/info_user.php"><i class="fas fa-user"></i> Thông Tin</a>
    <a href="src/network-config.php"><i class="fas fa-network-wired"></i> Cấu Hình IP</a>
    <a href="/docs/tos.html"><i class="fas fa-file-contract"></i> Điều khoản dịch vụ</a>
    <a href="index.php?logout=true"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
</div>
<script src = app/index/taskBar.js></script>
<div class="search"><a href="src/search.php"> Tìm kiếm</a></div>
<style>
    .search {padding: 10px; transform: translate(110px,-40px);background-color: azure; /* Màu nền xanh lá */border-radius: 5px; /* Bo góc cho phần tử */text-align: center; /* Canh giữa */max-width: 90px;box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); /* Tạo bóng cho phần tử */transition: background-color 0.3s ease; /* Thêm hiệu ứng chuyển màu nền */}
</style>
        <h2>Các bài viết</h2>
        <?php if ($posts->num_rows > 0): ?>
    <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="post">
            <h3><?php echo htmlspecialchars($post['content']); ?></h3>
            <p><?php echo htmlspecialchars($post['description']); ?></p>
            
            <!-- Hiển thị liên kết tải xuống nếu có tệp tin -->
<?php if (!empty($post['file'])): ?>
    <p>Tệp đính kèm: 
        <a href="uploads/<?php echo rawurlencode(basename($post['file'])); ?>" 
           download 
           onclick="return confirmDownload('<?php echo htmlspecialchars(basename($post['file'])); ?>')">
            <?php echo htmlspecialchars(basename($post['file'])); ?>
        </a>
    </p>
<?php endif; ?>

<script>function confirmDownload(fileName) {const userConfirmed = confirm(`Cảnh báo: Tệp "${fileName}" có thể không an toàn. Bạn có chắc chắn muốn tải xuống không?`);return userConfirmed;}</script>

            <?php
                // Định dạng ngày tháng
                $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $post['created_at']);
                $formattedDate = $createdAt ? $createdAt->format('d/n/Y | H:i:s') : 'Ngày không hợp lệ';
            ?>
            <small>
                Đăng bởi: 
                <a href="src/profile.php?username=<?php echo urlencode($post['username']); ?>" target="_blank">
                    <?php echo htmlspecialchars($post['username']); ?>
                </a> vào <?php echo htmlspecialchars($formattedDate); ?>
            </small>

            <!-- Thêm dòng chữ "Xem thêm" với liên kết tới view.php -->
            <small>
                <a href="src/view.php?id=<?php echo intval($post['id']); ?>" class="read-more">Xem thêm</a>
            </small>

            <?php if ($post['username'] == $_SESSION['username']): ?>
                <form method="get" action="index.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
                    <input type="hidden" name="delete" value="<?php echo intval($post['id']); ?>">
                    <button type="submit" class="delete-button">Xóa bài viết</button>
                </form>

<?php endif; ?>

</div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-posts">Chưa có bài viết nào.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>