<?php
session_start();
include '../config.php'; // Kết nối database từ file config
include '../app/view/php.php';
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
    <title>View Post</title>
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../app/view/styles.css">
</head>
<body>
<div class="container">
    <h1>Bài viết</h1>
    <div class="post">
        <h2><?php echo htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($post['description'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Tác giả:</strong> <?php echo htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8'); ?></p>
        <p><strong>Ngày tạo:</strong> <?php echo htmlspecialchars($post['created_at'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($post['file']): ?>
            <p><strong>Tệp đính kèm: </strong><a href="../uploads/<?php echo htmlspecialchars($post['file'], ENT_QUOTES, 'UTF-8'); ?>" download><?php echo htmlspecialchars($post['file'], ENT_QUOTES, 'UTF-8'); ?></a></p>
        <?php endif; ?>
        <?php if ($isOwner): ?>
            <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_post=1" class="btn btn-danger btn-delete">Xóa bài viết</a>
        <?php endif; ?>
    </div>

      <!-- Liên kết phân trang -->
      <nav>
    <ul class="pagination">
        <!-- Nút đến trang đầu tiên -->
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=1">&laquo; Đầu</a>
        </li>
        <?php endif; ?>

        <?php 
        // Hiển thị trang đầu tiên nếu không phải trang đầu tiên
        if ($page > 4) echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=1">1</a></li>';
        
        // Hiển thị dấu "..." nếu có trang ở giữa
        if ($page > 4) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        
        // Các trang trước trang hiện tại (hiển thị 3 trang trước đó)
        for ($i = max(1, $page - 3); $i < $page; $i++): ?>
        <li class="page-item">
            <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>

        <!-- Trang hiện tại -->
        <li class="page-item active">
            <a class="page-link" href="#"><?php echo $page; ?></a>
        </li>

        <!-- Các trang sau trang hiện tại (hiển thị 3 trang tiếp theo) -->
        <?php for ($i = $page + 1; $i <= min($totalPages, $page + 3); $i++): ?>
        <li class="page-item">
            <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>

        <?php 
        // Hiển thị dấu "..." nếu có trang ở giữa
        if ($page < $totalPages - 3) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

        // Hiển thị trang cuối cùng nếu không phải trang cuối cùng
        if ($page < $totalPages - 3) echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
        ?>

        <!-- Nút đến trang cuối cùng -->
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

    <?php if ($isLoggedIn): ?>
        <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <textarea name="comment" placeholder="Viết bình luận..." required></textarea>
    <button type="submit">Bình luận</button>
</form>

    <?php endif; ?>

    <div class="comments">
        <?php while ($comment = $commentsQuery->fetch_assoc()): ?>
            <div class="comment">
                <p><strong><?php echo htmlspecialchars($comment['username'], ENT_QUOTES, 'UTF-8'); ?></strong> (<?php echo htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8'); ?>)</p>
                <p><?php echo preg_replace_callback('/https?:\/\/[^\s]+/', function ($matches) {
                    return '<a href="' . htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '" target="_blank">' . htmlspecialchars($matches[0], ENT_QUOTES, 'UTF-8') . '</a>';
                }, htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8')); ?></p>

                <?php if ($isLoggedIn && $_SESSION['username'] === $comment['username']): ?>
                    <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&delete_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-danger btn-delete">Xóa bình luận</a>
                    <a href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&edit_comment=<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-warning btn-edit">Chỉnh sửa bình luận</a>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['edit_comment']) && $_GET['edit_comment'] == $comment['id'] && $_SESSION['username'] === $comment['username']): ?>
                <form action="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>" method="POST">
    <!-- CSRF token -->
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

    <textarea name="comment_content" required><?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
    <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['id'], ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" name="edit_comment">Cập nhật bình luận</button>
</form>

            <?php endif; ?>
        <?php endwhile; ?>
    </div>






</div>

<script src="/asset/js/popper.min.js"></script>
<script src="/asset/js/Bootstrap.bundle.min.js"></script>
</body>
</html>
