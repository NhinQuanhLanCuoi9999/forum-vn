<?php
session_start();
include '../config.php'; // Kết nối database từ file config
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

// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['username'])) {
    // Nếu chưa có session username => chưa đăng nhập
    $userLoggedIn = false;
    $isVerified = false;
} else {
    $userLoggedIn = true;
    // Kiểm tra trạng thái xác minh của tài khoản
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $isVerified = ($result && $result['is_active'] == 1);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></title>
  <meta name="description" content="<?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?>">
  <link rel="stylesheet" href="../asset/css/Poppins.css">
  <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="../app/_USERS_LOGIC/view/styles.css">
</head>

<body>
<div class="container">
  <h1>Bài viết</h1>
  <div class="post">
    <!-- Nút về trang index -->
    <div class="mt-3">
      <a href="index.php" class="btn btn-primary">Về trang chủ</a>
    </div>
    <h2><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></h2>
    <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></p>
    <p><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
    <?php if ($post['file']): ?>
      <p><strong>Tệp đính kèm: </strong>
        <a href="../uploads/<?php echo htmlspecialchars($post['file'], ENT_QUOTES, 'UTF-8'); ?>" download>
          <?php echo htmlspecialchars($post['file'], ENT_QUOTES, 'UTF-8'); ?>
        </a>
      </p>
    <?php endif; ?>
    <?php if ($isOwner): ?>
      <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_post=1" class="btn btn-danger btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">Xóa bài viết</a>
    <?php endif; ?>
  </div>

  <!-- Liên kết phân trang cho bài viết -->
  <nav>
    <ul class="pagination">
      <?php if ($page > 1): ?>
        <li class="page-item">
          <a class="page-link" href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=1">&laquo; Đầu</a>
        </li>
      <?php endif; ?>

      <?php 
      if ($page > 4) echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=1">1</a></li>';
      if ($page > 4) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
      for ($i = max(1, $page - 3); $i < $page; $i++): ?>
        <li class="page-item">
          <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item active">
        <a class="page-link" href="#"><?php echo $page; ?></a>
      </li>

      <?php for ($i = $page + 1; $i <= min($totalPages, $page + 3); $i++): ?>
        <li class="page-item">
          <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php endfor; ?>

      <?php 
      if ($page < $totalPages - 3) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
      if ($page < $totalPages - 3) echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
      ?>
      
      <?php if ($page < $totalPages): ?>
        <li class="page-item">
          <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $totalPages; ?>">Cuối &raquo;</a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>

  <?php if (isset($_SESSION['error'])): ?>
    <p class="error"><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?></p>
  <?php elseif (isset($_SESSION['success'])): ?>
    <p class="success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></p>
  <?php endif; ?>

  <?php if (!$userLoggedIn): ?>
    <!-- Nếu user chưa đăng nhập -->
    <p class="alert alert-warning">Bạn chưa đăng nhập. Vui lòng <a href="/">đăng nhập</a>!</p>
  <?php elseif (!$isVerified): ?>
    <!-- Nếu user đăng nhập nhưng chưa xác minh -->
    <p class="alert alert-warning">Tài khoản của bạn chưa được xác minh. Vui lòng <a href="/src/verify.php">vào đây</a> để xác minh!</p>
  <?php else: ?>
    <!-- Nếu user đã đăng nhập và xác minh -->
    <?php if ($isLoggedIn): ?>
      <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
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
            <!-- Nút bật toàn bộ mục reply (form + danh sách reply) -->
            <button class="btn btn-link p-0" data-bs-toggle="collapse" data-bs-target="#replySection-<?php echo $comment['id']; ?>">Trả lời</button>
          <?php endif; ?>

          <?php if ($isLoggedIn && $_SESSION['username'] === $comment['username']): ?>
            <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-delete btn-sm">Xóa bình luận</a>
            <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&edit_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning btn-edit btn-sm">Chỉnh sửa</a>
          <?php endif; ?>

          <!-- Phần reply ẩn ban đầu: bao gồm form nhập và danh sách reply -->
          <div id="replySection-<?php echo $comment['id']; ?>" class="collapse mt-2">
            <!-- Form gửi reply -->
            <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
              <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
              <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>">
              <textarea name="reply_content" class="form-control" placeholder="Viết trả lời..." required></textarea>
              <button type="submit" name="submit_reply" class="btn btn-primary mt-2">Gửi trả lời</button>
            </form>

            <!-- Hiển thị danh sách reply (luôn phân trang, 5 reply/trang) -->
            <div class="replies ml-5 mt-3">
              <?php
                // Đếm tổng số reply cho comment này
                $countResult = $conn->query("SELECT COUNT(*) AS total FROM replies WHERE comment_id = " . intval($comment['id']));
                $row = $countResult->fetch_assoc();
                $totalReplies = $row ? intval($row['total']) : 0;
                
                $replyPage = 1;
                if (isset($_POST['older_reply_page'][$comment['id']])) {
                    $replyPage = intval($_POST['older_reply_page'][$comment['id']]);
                    if ($replyPage < 1) { $replyPage = 1; }
                }
                $limit = 5;
                $offset = ($replyPage - 1) * $limit;
                // Lấy danh sách reply theo phân trang, sắp xếp DESC (reply mới nhất đứng đầu)
                $allRepliesQuery = $conn->query("SELECT * FROM replies WHERE comment_id = " . intval($comment['id']) . " ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
                while ($reply = $allRepliesQuery->fetch_assoc()):
              ?>
                        <div class="reply mb-2">
                          <div class="card card-body bg-light">
                            <p>
                              <strong><?php echo htmlspecialchars($reply['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                              <small>(<?php echo htmlspecialchars($reply['created_at'], ENT_QUOTES, 'UTF-8'); ?>)</small>
                              <?php if ($isLoggedIn && $_SESSION['username'] === $reply['username']): ?>
                                <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_reply=<?php echo $reply['id']; ?>" class="btn btn-danger btn-delete btn-sm">Xóa</a>
                              <?php endif; ?>
                            </p>
                            <p><?php echo preg_replace_callback('/https?:\\/\\/[^\s]+/', function ($matches) {
                                    return '<a href="' . htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '" target="_blank">' .
                                           htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '</a>';
                                  }, htmlspecialchars($reply['content'], ENT_QUOTES, 'UTF-8')); ?></p>
                          </div>
                        </div>
              <?php
                endwhile;
                // Hiển thị phân trang nếu cần
                $totalPagesReplies = ceil($totalReplies / $limit);
                if ($totalPagesReplies > 1):
              ?>
                        <div class="reply-pagination mt-2">
                          <form method="POST" action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php for ($i = 1; $i <= $totalPagesReplies; $i++): ?>
                                <button type="submit" name="older_reply_page[<?php echo $comment['id']; ?>]" value="<?php echo $i; ?>" class="btn <?php echo ($i == $replyPage ? 'btn-primary' : 'btn-secondary'); ?> btn-sm">
                                    <?php echo $i; ?>
                                </button>
                            <?php endfor; ?>
                          </form>
                        </div>
              <?php
                endif;
              ?>
            </div>
          </div>

          <?php if (isset($_GET['edit_comment']) && $_GET['edit_comment'] == $comment['id'] && $_SESSION['username'] === $comment['username']): ?>
            <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
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

</div>

<script src="/asset/js/popper.min.js"></script>
<script src="/asset/js/Bootstrap.bundle.min.js"></script>
<script src="/asset/js/jquery.min.js"></script>
<script>
$(document).ready(function() {
  $(".collapse").each(function() {
    var id = $(this).attr("id");
    if (localStorage.getItem("collapse-" + id) === "open") {
      $(this).addClass("show");
    }
    $(this).on("shown.bs.collapse", function() {
      localStorage.setItem("collapse-" + id, "open");
    });
    $(this).on("hidden.bs.collapse", function() {
      localStorage.setItem("collapse-" + id, "closed");
    });
  });
});
</script>
</body>
</html>
