<?php
include '../app/view/LogicPHP/Auth2.php';
// Kiểm tra nếu có id trong URL
if (isset($_GET['id'])) {
    $postId = $_GET['id'];

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
        $content = $_POST['comment'];
        $post_id = $postId;

        // Kiểm tra nếu nội dung bình luận không rỗng và có chứa từ cấm
        if (!empty($content) && containsBadWords($content)) {
            $_SESSION['error'] = "Nội dung không phù hợp, vui lòng kiểm tra lại.";
        } else {
            // Định dạng nội dung bình luận
            $formatted_content = formatText($content);

            $stmt_insert_comment = $conn->prepare("INSERT INTO comments (post_id, content, username) VALUES (?, ?, ?)");
            $stmt_insert_comment->bind_param("iss", $post_id, $formatted_content, $_SESSION['username']);
            $stmt_insert_comment->execute();
            logAction("Thêm bình luận vào bài viết ID: $post_id bởi người dùng: {$_SESSION['username']}");
        }

        // Chuyển hướng sau khi bình luận để ngăn việc gửi form lặp lại
        header("Location: view.php?id=$postId");
        exit();
    }

    // Xóa bình luận
    if ($isLoggedIn && isset($_GET['delete_comment'])) {
        $commentId = $_GET['delete_comment'];
        $stmt_delete_comment = $conn->prepare("DELETE FROM comments WHERE id = ? AND username = ?");
        $stmt_delete_comment->bind_param("is", $commentId, $_SESSION['username']);
        $stmt_delete_comment->execute();
        logAction("Xóa bình luận ID: $commentId bởi người dùng: {$_SESSION['username']}");

        // Chuyển hướng để làm mới trang
        header("Location: view.php?id=$postId");
        exit();
    }

    // Xử lý xóa bài viết
    if ($isOwner && isset($_GET['delete_post'])) {
        $stmt_delete_post = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt_delete_post->bind_param("i", $postId);
        $stmt_delete_post->execute();
        logAction("Xóa bài viết ID: $postId bởi người dùng: {$_SESSION['username']}");

        header("Location: index.php"); // Quay lại trang chủ sau khi xóa
        exit;
    }

} else {
    echo "Không có bài viết nào.";
    exit;
}

?>