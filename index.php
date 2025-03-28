<?php
session_start();
include('config.php');
include('app/_USERS_LOGIC/index/php.php');

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

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../asset/css/Poppins.css">
  <link rel="stylesheet" href="/asset/css/FontAwesome.min.css">
  <link rel="stylesheet" href="/asset/css/Bootstrap.min.css">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link rel="icon" href="/favicon.png" type="image/png">
  <link rel="stylesheet" type="text/css" href="app/_USERS_LOGIC/index/styles.css">
  <link rel="stylesheet" type="text/css" href="app/_USERS_LOGIC/index/header.css">
  <script src="app/_USERS_LOGIC/index/js/URLConvert.js"></script>
  <script src="app/_USERS_LOGIC/index/js/Spoil.js"></script>
  <script src="app/_USERS_LOGIC/index/js/TextScale.js"></script>
  <script src="/asset/js/Bootstrap.bundle.min.js"></script>
  <script src="/asset/js/jquery.min.js"></script>
  <script src="app/_USERS_LOGIC/index/js/FileHandle.js"></script>
</head>
<body>
<?php if (isset($_SESSION['error_message'])): ?>
          <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
          <div style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
    <script>
        alert("<?php echo addslashes($_SESSION['success']); ?>");
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
<!-- Header -->
<header class="d-flex align-items-center justify-content-between p-3 bg-light border-bottom shadow-sm sticky-top rounded-3" style="z-index: 999;">
    <h1 class="h5 mb-0 text-primary fw-bold"><?php echo htmlspecialchars($forum_name); ?></h1>
    <div class="d-flex align-items-center gap-2">
        <?php if (!empty($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'owner'])): ?>
            <a href="admin_tool/admin.php" class="btn btn-warning fw-semibold px-3">Admin Panel</a>
        <?php endif; ?>
        <?php if (empty($_SESSION['username'])): ?>
            <button id="loginBtn" class="btn btn-primary fw-semibold px-3">Đăng nhập | Đăng ký</button>
        <?php endif; ?>
    </div>
</header>

<!-- Menu chỉ hiện nếu đã đăng nhập -->
<?php if (!empty($_SESSION['username'])): ?>
    <nav class="d-flex align-items-center justify-content-center gap-4 p-2 bg-white border-bottom shadow-sm sticky-top rounded-3" style="top: 56px; z-index: 998;">
        <?php 
        $menuItems = [
            ['src/info_user.php', 'fas fa-user', 'Thông Tin'],
            ['src/network-config.php', 'fas fa-network-wired', 'Cấu Hình IP'],
            ['/docs/tos.html', 'fas fa-file-contract', 'Điều khoản dịch vụ'],
            ['index.php?logout=true', 'fas fa-sign-out-alt', 'Đăng xuất'],
            ['src/search.php', 'fas fa-search', 'Tìm kiếm']
        ];
        foreach ($menuItems as $item): ?>
            <a href="<?php echo $item[0]; ?>" class="text-dark text-decoration-none fw-semibold d-flex align-items-center gap-1 px-3 py-2 rounded-3 transition-all hover-bg-light">
                <i class="<?php echo $item[1]; ?>"></i> <?php echo $item[2]; ?>
            </a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>


  <!-- Modal cho Đăng nhập/Đăng ký -->
  <?php if (!isset($_SESSION['username'])): ?>
  <div id="authModal" class="modal-auth">
    <div class="modal-auth-content">
      <span class="modal-close" id="authClose">&times;</span>
      <!-- 2 tab: Login và Register -->
      <div id="authTabs">
        <button id="tabLogin" class="btn btn-outline-primary">Đăng nhập</button>
        <button id="tabRegister" class="btn btn-outline-success">Đăng ký</button>
      </div>
      <div id="loginFormContainer" style="display: none; margin-top:20px;">
        <form id="login-form" method="post" action="index.php">
          <h2>Đăng nhập</h2>
          <input type="text" name="username" placeholder="Tên đăng nhập" required maxlength="50">
          <input type="password" name="password" placeholder="Mật khẩu" required>
          <a href="/src/forget_pass.php">Quên mật khẩu?</a><br>
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <button type="submit" name="login" class="btn btn-primary mt-2">Đăng nhập</button>
        </form>
      </div>
      <div id="registerFormContainer" style="display: none; margin-top:20px;">
        <form id="register-form" method="post" action="index.php">
          <h2>Đăng ký</h2>
          <input type="text" name="username" placeholder="Tên đăng nhập" required pattern="^[a-zA-Z0-9]{5,30}$" title="Chỉ được nhập chữ, số, không dấu & không khoảng trắng. Từ 5 đến 30 ký tự nhé!">
          <input type="password" name="password" id="password" placeholder="Mật khẩu" required minlength="6" maxlength="30" pattern="^[a-zA-Z0-9]{6,30}$" title="Chỉ được nhập chữ, số, không dấu & không khoảng trắng. Từ 6 đến 30 ký tự nhé!">
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu" required>
          <input type="email" name="gmail" placeholder="Email" required>
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
          <label style="display:block; margin-top:10px;">
            <input type="checkbox" id="agreeCheckbox"> 
            Bằng cách nhấn vào nút này, bạn đồng ý <a href="/docs/tos.html" target="_blank"><strong>Điều khoản dịch vụ</strong></a>
          </label>
          <button type="submit" name="register" id="registerSubmit" class="btn btn-success mt-2" disabled>Đăng ký</button>
          <div id="passwordStrengthContainer" style="display:none;">
            <progress id="passwordStrength" value="0" max="100"></progress>
            <span id="passwordStrengthText"></span>
          </div>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Phần nội dung chính, có ID để bật hiệu ứng blur -->
  <div id="mainContainer" class="container" style="margin-top:20px;">
    <?php if (!empty($misc_name)) : ?>
      <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title" id="alertModalLabel">Thông báo</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <?= $misc_name; ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" onclick="closeAlert()">Đóng trong 2 giờ</button>
            </div>
          </div>
        </div>
      </div>
      <script src="app/_USERS_LOGIC/index/js/Alert.js"></script>
    <?php endif; ?>

    <?php if (isset($_SESSION['username'])): ?>
      <!-- Nếu đã đăng nhập: hiển thị form đăng bài -->
      <form action="index.php" method="POST" enctype="multipart/form-data">
        <h2>Đăng bài viết</h2>
        <!-- Ô nhập giới hạn 500 ký tự -->
        <div id="postContent" contenteditable="true" class="editable-input" placeholder="Nội dung bài viết"></div>
        <input type="hidden" name="content" id="hiddenInput">
        <p id="charCount">0/500</p>
        <!-- Ô nhập giới hạn 4096 ký tự -->
        <div id="postDescription" contenteditable="true" class="editable-input" placeholder="Mô tả ngắn"></div>
        <input type="hidden" name="description" id="hiddenDescription">
        <p id="descCharCount">0/4096</p>
        <label for="file">Chọn tệp để tải lên:</label>
        <input type="file" name="file" id="file">
        <input type="hidden" name="csrf_token2" value="<?php echo $_SESSION['csrf_token2']; ?>">
        <button type="submit" name="post" class="btn btn-primary">Đăng bài</button>
      </form>
      
      
    <?php endif; ?>

    <!-- Hiển thị danh sách các bài viết -->
    <h2>Các bài viết</h2>
    <?php if ($posts->num_rows > 0): ?>
      <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="post">
          <div class="post-container">
            <h3><?php echo htmlspecialchars($post['content']); ?></h3>
            <p><?php echo htmlspecialchars($post['description']); ?></p>
          </div>
          <?php if (!empty($post['file'])): ?>
            <p>Tệp đính kèm: 
              <a href="uploads/<?php echo rawurlencode(basename($post['file'])); ?>" download class="file-link">
                <?php echo htmlspecialchars(basename($post['file'])); ?>
              </a>
            </p>
          <?php endif; ?>
          <?php
            $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $post['created_at']);
            $formattedDate = $createdAt ? $createdAt->format('d/n/Y | H:i:s') : 'Ngày không hợp lệ';
          ?>
          <small>
            Đăng bởi: 
            <a href="src/profile.php?username=<?php echo urlencode($post['username']); ?>" target="_blank">
              <?php echo htmlspecialchars($post['username']); ?>
            </a> vào <?php echo htmlspecialchars($formattedDate); ?>
          </small>
          <small>
            <a href="src/view.php?id=<?php echo intval($post['id']); ?>" class="read-more">Xem thêm</a>
          </small>
          <?php if (isset($_SESSION['username']) && $post['username'] == $_SESSION['username']): ?>
            <form method="get" action="index.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
              <input type="hidden" name="delete" value="<?php echo intval($post['id']); ?>">
              <button type="submit" class="delete-button btn btn-danger">Xóa bài viết</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="no-posts">Chưa có bài viết nào.</p>
    <?php endif; ?>
  </div>
  <script src="app/_USERS_LOGIC/index/js/taskBar.js"></script>
  <script src="app/_USERS_LOGIC/index/js/Toogle.js"></script>
  <script>
   
  </script>
  <script src="app/_USERS_LOGIC/index/js/checkBox.js"></script>
</body>
</html>
