<?php
session_start();
include '../config.php'; // Kết nối database từ file config
include '../app/view/php.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post</title>
    <link rel="stylesheet" type="text/css" href="../app/view/styles.css">
</head>
<body>
    <div class="container">
        <h1>Bài viết</h1>
        <div class="post">
            <h2><?php echo $post['content']; ?></h2>
            <p><strong>Mô tả:</strong> <?php echo $post['description']; ?></p>
            <p><strong>Tác giả:</strong> <?php echo $post['username']; ?></p>
            <p><strong>Ngày tạo:</strong> <?php echo $post['created_at']; ?></p>

            <!-- Nếu là chủ bài viết, có thể xóa bài viết -->
            <?php if ($isOwner): ?>
                <a href="view.php?id=<?php echo $postId; ?>&delete_post=1" class="btn-delete">Xóa bài viết</a>
            <?php endif; ?>
        </div>

        <h2>Bình luận</h2>
        <?php if (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <?php if ($isLoggedIn): ?>
            <form action="view.php?id=<?php echo $postId; ?>" method="POST">
                <textarea name="comment" placeholder="Viết bình luận..." required></textarea>
                <button type="submit">Bình luận</button>
            </form>
        <?php endif; ?>

        <!-- Hiển thị bình luận -->
        <div class="comments">
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <p><strong><?php echo $comment['username']; ?></strong> (<?php echo $comment['created_at']; ?>)</p>
                    <p><?php echo $comment['content']; ?></p>

                    <!-- Xóa bình luận của bản thân -->
                    <?php if ($isLoggedIn && $_SESSION['username'] === $comment['username']): ?>
                        <a href="view.php?id=<?php echo $postId; ?>&delete_comment=<?php echo $comment['id']; ?>" class="btn-delete">Xóa bình luận</a>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
