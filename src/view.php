<?php
session_start();
include '../config.php'; // Kết nối database từ file config
include '../app/view/php.php';

// Hàm kiểm tra từ cấm
function containsBadWords($content) {
    // Đọc các từ cấm từ file badwords.txt
    $badWords = file('../badwords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($badWords as $word) {
        // Tạo pattern cho phép chỉ nhận diện từ cấm mà không bị dính với dấu cách hay dấu câu
        // Sử dụng \W để xác định ký tự không phải là chữ cái hoặc số
        $pattern = '/(?<![a-zA-Z0-9])' . preg_quote($word, '/') . '(?![a-zAZ0-9])/iu';
        if (preg_match($pattern, $content)) {
            return true;
        }
    }
    return false;
}

// Hàm ghi log vào file
function logEdit($postId, $commentId, $newContent) {
    $logFile = '../logs/edit.txt';
    $date = date('d/m/Y | H:i:s');
    $username = $_SESSION['username'];
    $logMessage = "[$date] [$username] đã cập nhật bình luận ID=$commentId trong post ID=$postId với nội dung: $newContent\n";

    // Kiểm tra và ghi log vào file
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        $_SESSION['error'] = "Không thể ghi log vào file!";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_comment'])) {
    $commentId = $_POST['comment_id'];
    $newContent = $_POST['comment_content'];
    $postId = $_GET['id'];  // Đảm bảo bạn có postId từ URL
    
    if (containsBadWords($newContent)) {
        $_SESSION['error'] = "Bình luận không được chứa từ cấm!";
    } else {
        // Kiểm tra nếu người dùng là chủ của bình luận
        $sql = "SELECT * FROM comments WHERE id = ? AND username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('is', $commentId, $_SESSION['username']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cập nhật bình luận nếu người dùng là chủ sở hữu
            $updateSql = "UPDATE comments SET content = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('si', $newContent, $commentId);
            if ($updateStmt->execute()) {
                // Ghi log khi cập nhật thành công
                logEdit($postId, $commentId, $newContent);

                $_SESSION['success'] = "Bình luận đã được cập nhật!";
                // Redirect về trang chính sau khi cập nhật
                header("Location: view.php?id=$postId");
                exit();
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật bình luận!";
            }
        } else {
            $_SESSION['error'] = "Bạn không có quyền chỉnh sửa bình luận này!";
        }
    }
}
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
