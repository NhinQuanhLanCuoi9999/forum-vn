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
    <!-- Thêm Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Thêm Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <!-- Hiển thị liên kết tải xuống nếu có tệp tin -->
<?php if ($post['file']): ?>
    <p><strong>Tệp đính kèm: </strong><a href="../uploads/<?php echo htmlspecialchars($post['file']); ?>" download><?php echo htmlspecialchars($post['file']); ?></a></p>
<?php endif; ?>
        <?php if ($isOwner): ?>
            <a href="view.php?id=<?php echo $postId; ?>&delete_post=1" class="btn btn-danger btn-delete">Xóa bài viết</a>
        <?php endif; ?>
    </div>

    <h2>Bình luận</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php elseif (isset($_SESSION['success'])): ?>
        <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
        <form action="view.php?id=<?php echo $postId; ?>" method="POST">
            <textarea name="comment" placeholder="Viết bình luận..." required></textarea>
            <button type="submit">Bình luận</button>
        </form>
    <?php endif; ?>

    <div class="comments">
        <?php while ($comment = $comments->fetch_assoc()): ?>
            <div class="comment">
                <p><strong><?php echo $comment['username']; ?></strong> (<?php echo $comment['created_at']; ?>)</p>
                <p><?php echo preg_replace_callback('/https?:\/\/[^\s]+/', function ($matches) {
                    return '<a href="' . $matches[0] . '" target="_blank">' . $matches[0] . '</a>';
                }, $comment['content']); ?></p>

                <?php if ($isLoggedIn && $_SESSION['username'] === $comment['username']): ?>
                    <a href="view.php?id=<?php echo $postId; ?>&delete_comment=<?php echo $comment['id']; ?>" class="btn btn-danger btn-delete">Xóa bình luận</a>
                    <a href="view.php?id=<?php echo $postId; ?>&edit_comment=<?php echo $comment['id']; ?>" class="btn btn-warning btn-edit">Chỉnh sửa bình luận</a>
                <?php endif; ?>
            </div>

            <?php if (isset($_GET['edit_comment']) && $_GET['edit_comment'] == $comment['id'] && $_SESSION['username'] === $comment['username']): ?>
                <form action="view.php?id=<?php echo $postId; ?>" method="POST">
                    <textarea name="comment_content" required><?php echo $comment['content']; ?></textarea>
                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                    <button type="submit" name="edit_comment">Cập nhật bình luận</button>
                </form>
            <?php endif; ?>
        <?php endwhile; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
