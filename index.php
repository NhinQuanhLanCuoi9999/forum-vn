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
However, if you redistribute the source code, you must retain this license.  */


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
  <script src="app/_USERS_LOGIC/index/Toogle.js"></script>
  <script src="app/_USERS_LOGIC/index/Refersh.js"></script>
  <script src="app/_USERS_LOGIC/index/Spoil.js"></script>
  <script src="/asset/js/Bootstrap.bundle.min.js"></script>

</head>
<body>
  <div id="mobile-warning">
    Vui lòng bật chế độ xem trên máy tính
  </div>
  <script src="app/_USERS_LOGIC/index/Size.js"></script>
  <div class="container">
  <?php if (!empty($misc_name)) :    ?>
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
    <script src="app/_USERS_LOGIC/index/Alert.js"></script>
<?php endif; ?>


    <h1 class="text-center mb-4 fade-in"><?php echo htmlspecialchars($forum_name); ?></h1>

    <?php if (!isset($_SESSION['username'])): ?>
      <!-- Nếu chưa đăng nhập: hiển thị form đăng nhập/đăng ký -->
      <?php
      if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      ?>
      <div id="auth-forms">
        <form id="login-form" method="post" action="index.php" style="display: block;">
          <h2>Đăng nhập</h2>
          <input type="text" name="username" placeholder="Tên đăng nhập" required maxlength="50">
          <input type="password" name="password" placeholder="Mật khẩu" required>
          <a href="/src/forget_pass.php">Quên mật khẩu?</a><br>
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <button type="submit" name="login">Đăng nhập</button>
          <p>Chưa có tài khoản? <span class="toggle-link" style="color: red;" onclick="toggleForms()">Đăng ký</span></p>
        </form>
       <form id="register-form" method="post" action="index.php" style="display: none;">
  <h2>Đăng ký</h2>
  <input type="text" name="username" placeholder="Tên đăng nhập" required pattern="^[a-zA-Z0-9]{5,30}$" title="Chỉ được nhập chữ, số, không dấu & không khoảng trắng. Từ 5 đến 30 ký tự nhé!">
  <input type="password" name="password" id="password" placeholder="Mật khẩu" required minlength="6" maxlength="30" pattern="^[a-zA-Z0-9]{6,30}$" title="Chỉ được nhập chữ, số, không dấu & không khoảng trắng. Từ 6 đến 30 ký tự nhé!">
  <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu" required>
  <input type="email" name="gmail" placeholder="Email" required>
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
  <label>
    <!-- Đã xóa inline onclick -->
    <input type="checkbox" id="agreeCheckbox"> 
    Bằng cách nhấn, bạn đồng ý <a href="/docs/tos.html" target="_blank"><strong>Điều khoản dịch vụ</strong></a>
  </label>
  <button type="submit" name="register" id="registerBtn" disabled style="background-color: #9e9e9e;">Đăng ký</button>
  <p>Đã có tài khoản? <span class="toggle-link" style="color: red;" onclick="toggleForms()">Đăng nhập</span></p>
  <div id="passwordStrengthContainer" style="display:none;">
    <progress id="passwordStrength" value="0" max="100"></progress>
    <span id="passwordStrengthText"></span>
  </div>
</form>

<script src="app/_USERS_LOGIC/index/checkBox.js"></script>

        <?php if (isset($_SESSION['error'])): ?>
          <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
          <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <!-- Nếu đã đăng nhập: hiển thị form đăng bài -->
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
        <label for="file">Chọn tệp để tải lên:</label>
        <input type="file" name="file" id="file">
        <input type="hidden" name="csrf_token2" value="<?php echo $_SESSION['csrf_token2']; ?>">
        <button type="submit" name="post">Đăng bài</button>
      </form>
      
      <!-- Nút Tùy chọn và Tìm kiếm được cố định ngay dưới form đăng bài -->
      <div class="fixed-buttons">
        <button id="optionsBtn">Tùy chọn</button>
        <div id="optionsMenu" class="dropdown-content">
          <a href="src/info_user.php"><i class="fas fa-user"></i> Thông Tin</a>
          <a href="src/network-config.php"><i class="fas fa-network-wired"></i> Cấu Hình IP</a>
          <a href="/docs/tos.html"><i class="fas fa-file-contract"></i> Điều khoản dịch vụ</a>
          <a href="index.php?logout=true"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
        <div class="search"><a href="src/search.php">Tìm kiếm</a></div>
      </div>
    <?php endif; ?>

    <!-- Hiển thị danh sách các bài viết -->
    <h2>Các bài viết</h2>
    <?php if ($posts->num_rows > 0): ?>
      <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="post">
          <h3><?php echo htmlspecialchars($post['content']); ?></h3>
          <p><?php echo htmlspecialchars($post['description']); ?></p>
          <?php if (!empty($post['file'])): ?>
            <p>Tệp đính kèm: 
              <a href="uploads/<?php echo rawurlencode(basename($post['file'])); ?>" 
                 download 
                 onclick="return confirmDownload('<?php echo htmlspecialchars(basename($post['file'])); ?>')">
                <?php echo htmlspecialchars(basename($post['file'])); ?>
              </a>
            </p>
          <?php endif; ?>
          <script>
            function confirmDownload(fileName) {
              return confirm(`Cảnh báo: Tệp "${fileName}" có thể không an toàn. Bạn có chắc muốn tải xuống không?`);
            }
          </script>
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
              <button type="submit" class="delete-button">Xóa bài viết</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="no-posts">Chưa có bài viết nào.</p> <br> <br>
    <?php endif; ?>
  </div>
  <script src="app/_USERS_LOGIC/index/taskBar.js"></script>
</body>
</html>
