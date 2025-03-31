<?php
session_start();
include '../config.php'; 
include '../app/_USERS_LOGIC/view/php.php';
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?php if (isset($error_message)) {echo "Lỗi";} else {echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8');}?>
  </title>
  <meta name="description" content="<?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="../asset/css/Poppins.css">
  <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../app/_USERS_LOGIC/view/styles.css">
</head>
<body>
<div class="container">
  <?php if (isset($error_message)): ?>
    <!-- Hiển thị thông báo lỗi nếu không tìm thấy bài viết -->
    <div class="alert alert-danger mt-3" role="alert">
      <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="text-center mt-3">
      <a href="index.php" class="btn btn-primary">Về trang chủ</a>
    </div>
  <?php else: ?>
    <?php if (isset($_POST['show_edit_post']) && $isOwner): ?>
      <!-- Form chỉnh sửa bài đăng -->
      <h1>Sửa bài đăng</h1>
      <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="show_edit_post" value="1">
        <div class="mb-3">
            <label for="content" class="form-label">Nội dung bài đăng</label>
            <textarea name="content" id="content" class="form-control" required><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea name="description" id="description" class="form-control"><?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Tệp đính kèm (nếu cần thay đổi)</label>
            <input type="file" name="file" id="file" class="form-control">
        </div>
        <!-- Switch kích hoạt/vô hiệu hóa -->
        <div class="mb-3">
            <label class="form-label">Phần bình luận</label>
            <?php if ($post['status'] == 2): ?>
                <p class="text-danger fw-bold">Bạn không thể thay đổi mục này vì đã bị chặn bởi Quản trị viên.</p>
            <?php else: ?>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="statusSwitch" name="status" value="1" <?php echo ($post['status'] == '1') ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="statusSwitch">Vô hiệu hóa</label>
                </div>
            <?php endif; ?>
        </div>
        <button type="submit" name="edit_post" class="btn btn-success">Cập nhật bài đăng</button>
        <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">Hủy</a>
      </form>
    <?php else: ?>
      <h1>Bài viết</h1>
      <div class="post">
        <!-- Nút về trang index -->
        <div class="mt-3">
          <a href="index.php" class="btn btn-primary">Về trang chủ</a>
        </div>
        <div class="post-wrapper">
          <h2><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></h2>
          <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($post['file']): ?>
        <?php 
        $filePath = "../uploads/" . $safeFileName;
        if (isImage($filePath)): ?>
            <!-- Hiển thị ảnh với modal -->
            <div class="text-center mb-3">
                <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal" onclick="updateModalImage('<?= $filePath ?>')">
                    <img src="<?= $filePath ?>" alt="<?= $cleanName ?>" style="max-width:100%; height:auto;">
                </a>
            </div>
        <?php elseif (isVideo($filePath)): ?>
            <!-- Hiển thị video -->
            <div class="text-center mb-3">
                <video controls style="max-width:100%; height:auto;">
                    <source src="<?= $filePath ?>" type="<?= mime_content_type($filePath) ?>">
                    Trình duyệt của bạn không hỗ trợ video.
                </video>
            </div>
        <?php elseif (isAudio($filePath)): ?>
            <!-- Hiển thị audio -->
            <div class="text-center mb-3">
                <audio controls style="width:100%;">
                    <source src="<?= $filePath ?>" type="<?= mime_content_type($filePath) ?>">
                    Trình duyệt của bạn không hỗ trợ audio.
                </audio>
            </div>
        <?php else: ?>
                <p><strong>Tệp đính kèm: </strong>
                    <a href="<?php echo $filePath; ?>" download onclick="return confirmDownload('<?php echo $cleanName; ?>')">
                        <?php echo $cleanName; ?>
                    </a>
                </p>
                <script>
                  function confirmDownload(fileName) {
                    return confirm(`Cảnh báo: Tệp "${fileName}" có thể không an toàn. Bạn có chắc muốn tải xuống không?`);
                  }
                </script>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($isOwner): ?>
          <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_post=1&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">Xóa bài viết</a>
          <!-- Nút sửa bài đăng gửi POST -->
          <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" method="POST" style="display: inline;">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="show_edit_post" value="1">
            <button type="submit" class="btn btn-warning btn-edit">Sửa bài đăng</button>
          </form>
        <?php endif; ?>
      </div>

      <!-- Phần phân trang cho bài viết -->
    <?php  renderPagination($postId, $page, $totalPages); ?>

      <!-- Hiển thị thông báo lỗi/success nếu có -->
      <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></p>
      <?php elseif (isset($_SESSION['success'])): ?>
        <p class="success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></p>
      <?php endif; ?>

      <!-- Phần bình luận và reply-->
      <?php if (!$userLoggedIn): ?>
        <p class="alert alert-warning">Bạn chưa đăng nhập. Vui lòng <a href="/">đăng nhập</a>!</p>
      <?php elseif (!$isVerified): ?>
        <p class="alert alert-warning">Tài khoản của bạn chưa được xác minh. Vui lòng <a href="/src/verify.php">vào đây</a> để xác minh!</p>
      <?php else: ?>
        <?php 
            // Kiểm tra trạng thái của bài post
            if ($postStatus == 1): ?>
                <p class="alert alert-danger">Bình luận đã bị tắt bởi chủ bài viết.</p>
                <?php exit; ?>
        <?php elseif ($postStatus == 2): ?>
                <p class="alert alert-danger">Bình luận đã bị tắt bởi Quản trị viên.</p>
                <?php exit; ?>
        <?php endif; ?>
        <!-- Nếu không bị chặn, hiển thị form bình luận -->
        <?php if ($userLoggedIn): ?>
            <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <textarea name="comment" placeholder="Viết bình luận..." required></textarea>
                <button type="submit">Bình luận</button>
            </form>
        <?php endif; ?>
        <div class="comments mt-4">
          <?php while ($comment = $commentsQuery->fetch_assoc()): ?>
            <div class="comment border p-3 mb-3">
              <p>
                <strong><?php echo htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <small>(<?php echo htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8'); ?>)</small>
              </p>
              <p><?php echo preg_replace_callback('/https?:\\/\\/[^\s]+/', function ($matches) {
                  return '<a href="' . htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') .
                         '" target="_blank">' . htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '</a>';
                }, htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')); ?></p>
              <?php if ($isLoggedIn): ?>
                <button class="btn btn-link p-0" data-bs-toggle="collapse" data-bs-target="#replySection-<?php echo $comment['id']; ?>">Trả lời</button>
              <?php endif; ?>
              <?php if ($isLoggedIn && $_SESSION['username'] === $comment['username']): ?>
                <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-delete btn-sm">Xóa bình luận</a>
                <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&edit_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning btn-edit btn-sm">Chỉnh sửa</a>
              <?php endif; ?>
              <!-- Phần reply -->
              <div id="replySection-<?php echo $comment['id']; ?>" class="collapse mt-2">
                <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>#replySection-<?php echo $comment['id']; ?>" method="POST">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>">
                  <textarea name="reply_content" class="form-control" placeholder="Viết trả lời..." required></textarea>
                  <button type="submit" name="submit_reply" class="btn btn-primary mt-2">Gửi trả lời</button>
                </form>
                <!-- Hiển thị danh sách reply -->
                <div class="replies ml-5 mt-3">
                  <?php
                    $countResult = $conn->query("SELECT COUNT(*) AS total FROM replies WHERE comment_id = " . intval($comment['id']));
                    $row = $countResult->fetch_assoc();
                    $totalReplies = $row ? intval($row['total']) : 0;
                    $replyPage = 1;
                    if (isset($_POST['older_reply_page'][$comment['id']])) {
                        $replyPage = intval($_POST['older_reply_page'][$comment['id']]);
                        if ($replyPage < 1) { $replyPage = 1; }
                    }
                    $limit = $replyPage * 5;
                    $allRepliesQuery = $conn->query("SELECT * FROM replies WHERE comment_id = " . intval($comment['id']) . " ORDER BY created_at DESC LIMIT $limit");
                    while ($reply = $allRepliesQuery->fetch_assoc()):
                  ?>
                    <div class="reply mb-2">
                      <div class="card card-body bg-light">
                        <p>
                          <strong><?php echo htmlspecialchars($reply['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                          <small>(<?php echo htmlspecialchars($reply['created_at'], ENT_QUOTES, 'UTF-8'); ?>)</small>
                          <?php if ($isLoggedIn && $_SESSION['username'] === $reply['username']): ?>
                            <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_reply=<?php echo $reply['id']; ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-delete btn-sm">Xóa</a>
                          <?php endif; ?>
                        </p>
                        <p><?php echo preg_replace_callback('/https?:\\/\\/[^\s]+/', function ($matches) {
                                return '<a href="' . htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '" target="_blank">' .
                                       htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '</a>';
                              }, htmlspecialchars($reply['content'], ENT_QUOTES, 'UTF-8')); ?></p>
                      </div>
                    </div>
                  <?php endwhile; ?>
                  <?php if ($totalReplies > $limit): ?>
                    <form method="POST" action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>#replySection-<?php echo $comment['id']; ?>">
                      <input type="hidden" name="older_reply_page[<?php echo $comment['id']; ?>]" value="<?php echo $replyPage + 1; ?>">
                      <button type="submit" class="btn btn-secondary btn-sm">Xem thêm</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>

              <script>$(document).ready(function(){if(window.location.hash) {$('html, body').animate({scrollTop: $(window.location.hash).offset().top}, 1000);}});</script>

              <?php if (isset($_GET['edit_comment']) && $_GET['edit_comment'] == $comment['id'] && $_SESSION['username'] === $comment['username']): ?>
                <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                  <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                  <textarea name="comment_content" class="form-control" required><?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                  <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>">
                  <button type="submit" name="edit_comment" class="btn btn-success mt-2">Cập nhật bình luận</button>
                </form>
              <?php endif; ?>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Modal Bootstrap full screen -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="imageModalLabel">Xem ảnh</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex justify-content-center align-items-center">
        <img id="modalImage" src="" class="img-fluid" style="max-width: 100vw; max-height: 100vh;">
      </div>
    </div>
  </div>
</div>


<script>function updateModalImage(src) {document.getElementById('modalImage').src = src;}</script>
<script src="/asset/js/popper.min.js"></script>
<script src="/asset/js/Bootstrap.bundle.min.js"></script>
<script src="/asset/js/jquery.min.js"></script>
<script src="/app/_USERS_LOGIC/view/Slide.js"></script>
</body>
</html>
