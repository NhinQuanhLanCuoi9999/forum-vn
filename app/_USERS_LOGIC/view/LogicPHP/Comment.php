<?php
include '../app/_USERS_LOGIC/view/LogicPHP/Comment2.php';
include_once 'RateLimit.php';


// Kiểm tra nếu có id trong URL
if (isset($_GET['id'])) {
    $postId = intval($_GET['id']); // Chuyển ID sang kiểu số nguyên để bảo vệ khỏi injection

    // Lấy thông tin bài viết theo id
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();

    // Nếu bài viết không tồn tại
    if (!$post) {
        echo "Bài viết không tồn tại.";
        exit;
    }

    // Lấy danh sách bình luận của bài viết
    $stmt_comments = $conn->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
    $stmt_comments->bind_param("i", $postId);
    $stmt_comments->execute();
    $comments = $stmt_comments->get_result();

    // Kiểm tra nếu người dùng đã đăng nhập
    $isLoggedIn = isset($_SESSION['username']);
    $isOwner = $isLoggedIn && $_SESSION['username'] === $post['username']; // Kiểm tra nếu người dùng là chủ bài đăng

    // Thêm bình luận
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
        $content = trim($_POST['comment']);
        $post_id = $postId;

        // Gọi hàm kiểm tra rate limit (được định nghĩa trong RateLimit.php)
        checkRateLimit($postId);

        // Kiểm tra CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            echo "<script>
                    alert('Invalid CSRF Token');
                    window.location.href='view.php?id=$postId';
                  </script>";
            exit();
        }

        // Kiểm tra nếu nội dung bình luận bị rỗng hoặc toàn khoảng trắng
        if (empty($content) || preg_match('/^\s*$/', $content)) {
            $_SESSION['error'] = "Bình luận không được để trống hoặc chỉ chứa khoảng trắng!";
        } elseif (strlen($content) > 2048) {
            $_SESSION['error'] = "Bình luận không được vượt quá 2048 ký tự!";
        } else {
            // Định dạng nội dung bình luận
            $formatted_content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

            // Thêm bình luận vào database
            $stmt_insert_comment = $conn->prepare("INSERT INTO comments (post_id, content, username) VALUES (?, ?, ?)");
            $stmt_insert_comment->bind_param("iss", $post_id, $formatted_content, $_SESSION['username']);
            $stmt_insert_comment->execute();

            // Ghi log bình luận
            $datetime = date("d/m/Y | H:i:s");
            logAction("[$datetime] Người dùng: {$_SESSION['username']} đã đăng bình luận vào bài viết ID: $post_id với nội dung: $formatted_content");

            $_SESSION['success'] = "Bình luận đã được thêm!";
        }

        // Chuyển hướng sau khi bình luận để tránh gửi form lặp lại
        header("Location: view.php?id=$postId");
        exit();
    }

    // Xóa bình luận
    if ($isLoggedIn && isset($_GET['delete_comment'])) {
        $commentId = intval($_GET['delete_comment']); // Kiểm tra và bảo vệ ID

        // Lấy nội dung bình luận trước khi xóa
        $stmt_get_comment = $conn->prepare("SELECT content FROM comments WHERE id = ? AND username = ?");
        $stmt_get_comment->bind_param("is", $commentId, $_SESSION['username']);
        $stmt_get_comment->execute();
        $result = $stmt_get_comment->get_result();

        if ($row = $result->fetch_assoc()) {
            $deleted_content = $row['content'];
        } else {
            $deleted_content = "[Không lấy được nội dung bình luận]";
        }

        // Xóa bình luận
        $stmt_delete_comment = $conn->prepare("DELETE FROM comments WHERE id = ? AND username = ?");
        $stmt_delete_comment->bind_param("is", $commentId, $_SESSION['username']);
        $stmt_delete_comment->execute();

        // Ghi log xóa bình luận
        $datetime = date("d/m/Y | H:i:s");
        logAction("[$datetime] Người dùng: {$_SESSION['username']} đã xóa bình luận ID: $commentId với nội dung: $deleted_content");

        $_SESSION['success'] = "Bình luận đã được xóa!";
        header("Location: view.php?id=$postId");
        exit();
    }

    // Xóa bài viết
    if ($isOwner && isset($_GET['delete_post'])) {
        // Lấy nội dung bài viết trước khi xóa
        $stmt_get_post = $conn->prepare("SELECT content FROM posts WHERE id = ?");
        $stmt_get_post->bind_param("i", $postId);
        $stmt_get_post->execute();
        $result = $stmt_get_post->get_result();

        if ($row = $result->fetch_assoc()) {
            $deleted_content = $row['content'];
        } else {
            $deleted_content = "[Không lấy được nội dung bài viết]";
        }

        // Xóa bài viết
        $stmt_delete_post = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt_delete_post->bind_param("i", $postId);
        $stmt_delete_post->execute();

        // Ghi log xóa bài viết
        $datetime = date("d/m/Y | H:i:s");
        logAction("[$datetime] Người dùng: {$_SESSION['username']} đã xóa bài viết ID: $postId với nội dung: $deleted_content");

        $_SESSION['success'] = "Bài viết đã được xóa!";
        header("Location: index.php"); // Quay lại trang chủ sau khi xóa
        exit;
    }
} else {
    header('Location: ../index.php');
    exit;
}
?>
