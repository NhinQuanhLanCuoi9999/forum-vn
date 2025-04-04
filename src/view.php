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

You are free to use, modify, and distribute this software under the terms of the GPL v3.0.  
However, if you redistribute the source code, you must retain this license.
*/
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>
    <?php if (isset($error_message)) { echo "Lỗi"; } else { echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); } ?>
  </title>
  <meta name="description" content="<?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="../asset/css/Poppins.css">
  <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../app/_USERS_LOGIC/view/styles.css">
</head>
<body>
<div class="container">
  <?php if (isset($error_message)): ?>
    <div class="alert alert-danger mt-3" role="alert">
      <?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <div class="text-center mt-3">
      <a href="index.php" class="btn btn-primary">Về trang chủ</a>
    </div>
  <?php else: ?>
    <?php if (isset($_POST['show_edit_post']) && $isOwner): ?>
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
        <div class="mt-3">
          <a href="index.php" class="btn btn-primary">Về trang chủ</a>
        </div>
        <div class="post-wrapper position-relative">
          <h2><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></h2>
          <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
          <p><strong>Lượt xem:</strong> <?php echo $viewCount; ?></p>
<?php 
// Hiển thị tệp đính kèm (hình ảnh, video, âm thanh hoặc liên kết tải xuống)
echo displayAttachment($post['file'], $safeFileName, $cleanName);
?>

          <?php 
          // Hiển thị các nút phản ứng (like, dislike)
          renderReactionButtons($postId, $totalLikes, $totalDislikes); 
          ?>


        <?php if ($isOwner): ?>
          <div class="dropdown mt-3">
              <button class="btn btn-secondary dropdown-toggle" type="button" id="postOptions" data-bs-toggle="dropdown" aria-expanded="false">
                  ⚙️ Tùy chọn
              </button>
              <ul class="dropdown-menu" aria-labelledby="postOptions">
                  <li>
                      <a class="dropdown-item text-danger" href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_post=1&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" 
                         onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
                          🗑 Xóa bài viết
                      </a>
                  </li>
                  <li>
                      <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
                          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                          <input type="hidden" name="show_edit_post" value="1">
                          <button type="submit" class="dropdown-item text-warning">✏️ Sửa bài đăng</button>
                      </form>
                  </li>
              </ul>
          </div>
        <?php endif; ?>
      </div>

      <?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id){
  $res = mysqli_query($conn, "SELECT status FROM posts WHERE id={$id}");
  if($res && ($row = mysqli_fetch_assoc($res)) && $row['status'] === '0'){
    renderPagination($id, $page, $totalPages);
  }
}
?>

      <?php if (isset($_SESSION['error'])): ?>
        <p class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></p>
      <?php elseif (isset($_SESSION['success'])): ?>
        <p class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></p>
      <?php endif; ?>

      <?php if (!$userLoggedIn): ?>
        <p class="alert alert-warning">Bạn chưa đăng nhập. Vui lòng <a href="/">đăng nhập</a>!</p>
      <?php elseif (!$isVerified): ?>
        <p class="alert alert-warning">Tài khoản của bạn chưa được xác minh. Vui lòng <a href="/src/verify.php">vào đây</a> để xác minh!</p>
      <?php else: ?>
        <?php if ($postStatus == 1): ?>
          <p class="alert alert-danger">Bình luận đã bị tắt bởi chủ bài viết.</p>
        <?php elseif ($postStatus == 2): ?>
          <p class="alert alert-danger">Bình luận đã bị tắt bởi Quản trị viên.</p>
        <?php else: ?>
          <?php if ($userLoggedIn): ?>
            <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <textarea name="comment" placeholder="Viết bình luận..." required></textarea>
              <button type="submit">Bình luận</button>
            </form>
          <?php endif; ?>
        <?php endif; ?>

        <?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if($id){
  $res = mysqli_query($conn, "SELECT status FROM posts WHERE id={$id}");
  if($res && ($row = mysqli_fetch_assoc($res)) && $row['status'] === '0'):
?>
<div class="comments mt-4">
  <?php while($comment = $commentsQuery->fetch_assoc()): ?>
    <div class="comment border p-3 mb-3">
      <p>
        <strong><?php echo htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
        <small>(<?php echo htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8'); ?>)</small>
      </p>
      <p><?php echo preg_replace_callback('/https?:\\/\\/[^\s]+/', function($matches){
          return '<a href="'.htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8').'" target="_blank">'
                 .htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8').'</a>';
        }, htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')); ?></p>
      <?php if($isLoggedIn): ?>
      <div class="dropdown">
        <button class="btn btn-link p-0 dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
          Tùy chọn
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
          <li><a class="dropdown-item" href="#" data-bs-toggle="collapse" data-bs-target="#replySection-<?php echo $comment['id']; ?>">Trả lời</a></li>
          <?php if($_SESSION['username'] === $comment['username']): ?>
          <li><a class="dropdown-item" href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>">Xóa bình luận</a></li>
          <li><a class="dropdown-item" href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&edit_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>">Chỉnh sửa</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endif; ?>
      <div id="replySection-<?php echo $comment['id']; ?>" class="collapse mt-2">
        <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>#replySection-<?php echo $comment['id']; ?>" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>">
          <textarea name="reply_content" class="form-control" placeholder="Viết trả lời..." required></textarea>
          <button type="submit" name="submit_reply" class="btn btn-primary mt-2">Gửi trả lời</button>
        </form>
        <div class="replies ml-5 mt-3">
          <?php
          $countRes = mysqli_query($conn, "SELECT COUNT(*) AS total FROM replies WHERE comment_id=".intval($comment['id']));
          $row = mysqli_fetch_assoc($countRes);
          $totalReplies = $row ? intval($row['total']) : 0;
          $replyPage = isset($_POST['older_reply_page'][$comment['id']]) ? max(1, intval($_POST['older_reply_page'][$comment['id']])) : 1;
          $limit = $replyPage * 5;
          $allReplies = mysqli_query($conn, "SELECT * FROM replies WHERE comment_id=".intval($comment['id'])." ORDER BY created_at DESC LIMIT $limit");
          while($reply = mysqli_fetch_assoc($allReplies)):
          ?>
          <div class="reply mb-2">
            <div class="card card-body bg-light">
              <p>
                <strong><?php echo htmlspecialchars($reply['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                <small>(<?php echo htmlspecialchars($reply['created_at'], ENT_QUOTES, 'UTF-8'); ?>)</small>
                <?php if($isLoggedIn && $_SESSION['username'] === $reply['username']): ?>
                <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_reply=<?php echo $reply['id']; ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-delete btn-sm">Xóa</a>
                <?php endif; ?>
              </p>
              <p><?php echo preg_replace_callback('/https?:\\/\\/[^\s]+/', function($matches){
                  return '<a href="'.htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8').'" target="_blank">'
                         .htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8').'</a>';
                }, htmlspecialchars($reply['content'], ENT_QUOTES, 'UTF-8')); ?></p>
            </div>
          </div>
          <?php endwhile; ?>
          <?php if($totalReplies > $limit): ?>
          <form method="POST" action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=<?php echo htmlspecialchars($page, ENT_QUOTES, 'UTF-8'); ?>#replySection-<?php echo $comment['id']; ?>">
            <input type="hidden" name="older_reply_page[<?php echo $comment['id']; ?>]" value="<?php echo $replyPage+1; ?>">
            <button type="submit" class="btn btn-secondary btn-sm">Xem thêm</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <script>
        $(document).ready(function(){
          if(window.location.hash){ 
            $('html, body').animate({ scrollTop: $(window.location.hash).offset().top }, 1000);
          }
        });
      </script>
      <?php if(isset($_GET['edit_comment']) && $_GET['edit_comment']==$comment['id'] && $_SESSION['username'] === $comment['username']): ?>
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
<?php endif; } ?>
      <?php endif; ?>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
      new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
</script>
<script src="/asset/js/popper.min.js"></script>
<script src="/asset/js/Bootstrap.bundle.min.js"></script>
<script src="/asset/js/jquery.min.js"></script>
<script src="/app/_USERS_LOGIC/view/Slide.js"></script>
</body>
</html>
