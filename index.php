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
*/
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Các font và icon -->
  <link rel="stylesheet" href="../asset/css/Poppins.css">
  <link rel="stylesheet" href="/asset/css/FontAwesome.min.css">
  <link rel="stylesheet" href="/asset/css/Bootstrap.min.css">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link rel="icon" href="/favicon.ico" type="image/png">
  <script src="/asset/js/jquery.min.js"></script>
  <script src="/asset/js/Bootstrap.bundle.min.js"></script>
  <script src="app/_USERS_LOGIC/index/js/URLConvert.js"></script>
  <script src="app/_USERS_LOGIC/index/js/Spoil.js"></script>
  <script src="app/_USERS_LOGIC/index/js/TextScale.js"></script>
  <script src="app/_USERS_LOGIC/index/js/FileHandle.js"></script>
  <script src="app/_USERS_LOGIC/index/js/SubWindows.js" defer></script>
  <script src="asset/js/Tailwindcss.js"></script>


</head>
<body>


<!-- Thông báo lỗi/ thành công -->
<div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

  <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
  <?php endif; ?>
  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
  <?php endif; ?>
</div>

<!-- Header + Menu (Tailwind ver.) -->
<header class="bg-white shadow-sm sticky top-0 left-0 z-50 w-full rounded-2">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center py-4">
      <h1 class="text-lg font-bold text-blue-600 m-0"><?php echo htmlspecialchars($forum_name); ?></h1>
      <div class="flex items-center space-x-2">
        <?php if (!empty($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'owner'])): ?>
          <a href="admin_tool/admin.php" class="bg-yellow-400 text-black font-semibold px-4 py-2 rounded hover:bg-yellow-500 transition">
            Admin Panel
          </a>
        <?php endif; ?>
        <?php if (empty($_SESSION['username'])): ?>
          <button id="loginBtn" class="bg-blue-600 text-white font-semibold px-4 py-2 rounded hover:bg-blue-700 transition">
            Đăng nhập | Đăng ký
          </button>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($_SESSION['username'])): ?>
      <nav class="flex justify-center py-2 flex-wrap gap-x-4 gap-y-2">
        <?php 
        $menuItems = [
          ['src/info_user.php', 'fas fa-user', 'Thông Tin'],
          ['src/network-config.php', 'fas fa-network-wired', 'Cấu Hình IP'],
          ['/docs/tos.html', 'fas fa-file-contract', 'Điều khoản dịch vụ'],
          ['src/search.php', 'fas fa-search', 'Tìm kiếm'],
          ['index.php?logout=true', 'fas fa-sign-out-alt', 'Đăng xuất']
        ];

        foreach ($menuItems as $item):
          $href = $item[0];
          $icon = $item[1];
          $label = $item[2];

          if ($href === 'src/search.php'):
        ?>
          <a href="javascript:void(0);" onclick="openSearchModalWithId('search')" class="text-gray-700 font-semibold px-3 py-1 hover:text-blue-600 transition">
            <i class="<?php echo $icon; ?>"></i> <?php echo $label; ?>
          </a>
        <?php else: ?>
          <a href="<?php echo $href; ?>" class="text-gray-700 font-semibold px-3 py-1 hover:text-blue-600 transition">
            <i class="<?php echo $icon; ?>"></i> <?php echo $label; ?>
          </a>
        <?php 
          endif;
        endforeach; ?>
      </nav>
    <?php endif; ?>
  </div>
</header>


<!-- Nếu có IFrame cần hiển thị -->
<?php renderIFrame(); ?>

<!-- Modal Authentication (Đăng nhập/Đăng ký) -->
<?php if (!isset($_SESSION['username'])): ?>
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="authModalLabel">Xác thực</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Tab nav cho Login và Register -->
        <ul class="nav nav-tabs mb-3" id="authTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tabLogin-tab" data-bs-toggle="tab" data-bs-target="#tabLogin" type="button" role="tab" aria-controls="tabLogin" aria-selected="true">Đăng nhập</button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tabRegister-tab" data-bs-toggle="tab" data-bs-target="#tabRegister" type="button" role="tab" aria-controls="tabRegister" aria-selected="false">Đăng ký</button>
          </li>
        </ul>
        <div class="tab-content" id="authTabsContent">
          <!-- Tab đăng nhập -->
          <div class="tab-pane fade show active" id="tabLogin" role="tabpanel" aria-labelledby="tabLogin-tab">
            <form id="login-form" method="post" action="index.php">
              <div class="mb-3 text-center">
                <h2>Đăng nhập</h2>
              </div>
              <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required maxlength="50">
              </div>
              <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
              </div>
              <div class="mb-3">
              <a href="#" id="forgotPasswordBtn" class="link-primary">Quên mật khẩu?</a>

              </div>
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <div class="d-grid gap-2">
                <button type="submit" name="login" class="btn btn-primary">Đăng nhập</button>
              </div>
              <div class="my-3 text-center fw-bold">HOẶC</div>
              <div class="d-grid gap-2">
                <a href="#" id="googleLoginBtn" class="btn btn-danger">
                  <img src="https://developers.google.com/identity/images/g-logo.png" width="20" class="me-2" alt="Google Logo">
                  Đăng nhập với Google
                </a>
              </div>
            </form>
          </div>
          <!-- Tab đăng ký -->
          <div class="tab-pane fade" id="tabRegister" role="tabpanel" aria-labelledby="tabRegister-tab">
            <form id="register-form" method="post" action="index.php">
              <div class="mb-3 text-center">
                <h2>Đăng ký</h2>
              </div>
              <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required pattern="^[a-zA-Z0-9]{5,30}$" title="Chỉ được nhập chữ, số, không dấu & không khoảng trắng. Từ 5 đến 30 ký tự nhé!">
              </div>
              <div class="mb-3">
                <input type="password" name="password" id="password" class="form-control" placeholder="Mật khẩu" required minlength="6" maxlength="30" pattern="^[a-zA-Z0-9]{6,30}$" title="Chỉ được nhập chữ, số, không dấu & không khoảng trắng. Từ 6 đến 30 ký tự nhé!">
              </div>
              <div class="mb-3">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu" required>
              </div>
              <div class="mb-3">
                <input type="email" name="gmail" class="form-control" placeholder="Email" required>
              </div>
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="agreeCheckbox">
                <label for="agreeCheckbox" class="form-check-label">
                  Bằng cách nhấn vào nút này, bạn đồng ý <a href="/docs/tos.html" target="_blank"><strong>Điều khoản dịch vụ</strong></a>
                </label>
              </div>
              <div class="d-grid">
                <button type="submit" name="register" id="registerSubmit" class="btn btn-success" disabled>Đăng ký</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  // Hiển thị modal khi bấm nút Đăng nhập | Đăng ký
  document.getElementById('loginBtn')?.addEventListener('click', function() {
    new bootstrap.Modal(document.getElementById('authModal')).show();
  });
  // Bật/tắt nút đăng ký dựa trên checkbox
  document.getElementById('agreeCheckbox')?.addEventListener('change', function(){
    document.getElementById('registerSubmit').disabled = !this.checked;
  });
</script>
<?php endif; ?>

<!-- Modal Thông báo (Alert) -->
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
<!-- Form Đăng bài cho người dùng đã đăng nhập -->
<?php if (isset($_SESSION['username'])): ?>
<div class="container my-4">
  <form action="index.php" method="POST" enctype="multipart/form-data" id="postForm">
    <h2 class="mb-3">Đăng bài viết</h2>
    
    <div class="mb-3">
      <div id="postContent" contenteditable="true" class="form-control" style="min-height:150px;" placeholder="Nội dung bài viết"></div>
      <input type="hidden" name="content" id="hiddenInput">
      <small id="charCount" class="text-muted">0/500</small>
    </div>
    
    <div class="mb-3">
      <div id="postDescription" contenteditable="true" class="form-control" style="min-height:80px;" placeholder="Mô tả ngắn"></div>
      <input type="hidden" name="description" id="hiddenDescription">
      <small id="descCharCount" class="text-muted">0/4096</small>
    </div>
    
    <div class="mb-3">
      <label for="file" class="form-label">Chọn tệp để tải lên:</label>
      <input type="file" name="file" id="file" class="form-control">
    </div>
    
    <input type="hidden" name="csrf_token2" value="<?php echo $_SESSION['csrf_token2']; ?>">
    
    <div class="d-grid">
      <button type="submit" name="post" class="btn btn-primary">Đăng bài</button>
    </div>
  </form>
</div>

<?php endif; ?>


<!-- Phân Trang và hiển thị bài viết -->
<div class="container my-5">
  <div class="row mb-4">
    <div class="col text-center">
      <?php renderPagination($current_section, $total_sections); ?>
    </div>
  </div>
  <h2 class="mb-4 text-center text-primary fw-bold text-uppercase">Các bài viết</h2>
  <?php if ($posts->num_rows > 0): ?>
    <div class="row gy-4">
      <?php while ($post = $posts->fetch_assoc()): ?>
        <div class="col-12">
        <div class="card shadow-lg border-0 rounded-4" style="background: linear-gradient(135deg, #e0f7fa 0%, #ffffff 100%);">
    <div class="card-body">
              <h5 class="card-title text-dark fw-bold" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($post['content'])); ?></h5>
              <p class="card-text text-muted fst-italic" style="white-space: pre-wrap;"><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
              <?php if (!empty($post['file'])): 
                  $filePath = 'uploads/' . basename($post['file']);
                  if (shouldDisplayInline($filePath)): ?>
                    <div class="mb-3">
                      <?php if (isImage($filePath)): ?>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="updateModalImage('<?= $filePath ?>')">
                          <img src="<?= $filePath ?>" class="img-fluid rounded border border-2 border-light" alt="Media">
                        </a>
                      <?php elseif (isVideo($filePath)): ?>
                        <video controls class="w-100 rounded border border-2 border-light">
                          <source src="<?= $filePath ?>" type="<?= mime_content_type($filePath) ?>">
                        </video>
                      <?php elseif (isAudio($filePath)): ?>
                        <audio controls class="w-100">
                          <source src="<?= $filePath ?>" type="<?= mime_content_type($filePath) ?>">
                        </audio>
                      <?php endif; ?>
                    </div>
              <?php endif; endif; ?>
              <?php 
                $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $post['created_at']);
                $formattedDate = $createdAt ? $createdAt->format('d/m/Y | H:i:s') : 'Ngày không hợp lệ';
              ?>
            </div>
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
              <small class="text-dark fw-semibold">
                Đăng bởi: 
                <a href="javascript:void(0);" onclick="openProfileModal('<?php echo urlencode($post['username']); ?>')" class="text-decoration-none text-primary">
                  <?php echo htmlspecialchars($post['username']); ?>
                </a> vào <?php echo htmlspecialchars($formattedDate); ?>
              </small>
              <div>
                <a href="javascript:void(0);" onclick="openSearchModalWithId(<?php echo intval($post['id']); ?>)" class="btn btn-outline-light btn-sm me-2">
                  Xem thêm
                </a>
                <?php if (isset($_SESSION['username']) && $post['username'] == $_SESSION['username']): ?>
                  <form method="get" action="index.php" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
                    <input type="hidden" name="delete" value="<?php echo intval($post['id']); ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Xóa bài viết</button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-info text-center" role="alert">
      Chưa có bài viết nào.
    </div>
  <?php endif; ?>
</div>

<!-- Modal full screen để hiển thị ảnh -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content bg-transparent border-0">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel">Xem ảnh</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex justify-content-center align-items-center">
        <img id="modalImage" src="" class="img-fluid rounded" alt="Modal Image">
      </div>
    </div>
  </div>
</div>

<script src="app/_USERS_LOGIC/index/js/taskBar.js"></script>
<script src="app/_USERS_LOGIC/index/js/Toogle.js"></script>
<script>function updateModalImage(src) {document.getElementById('modalImage').src = src;}</script>
<script src="app/_USERS_LOGIC/index/js/checkBox.js"></script>
</body>
</html>
