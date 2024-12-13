<?php
// Thêm bình luận
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    // Kiểm tra nếu nội dung bình luận không rỗng và có chứa từ cấm
    if (!empty($content) && containsBadWords($content)) {
        $_SESSION['error'] = "Nội dung không phù hợp, vui lòng kiểm tra lại.";
    } else {
        // Định dạng nội dung bình luận
        $formatted_content = formatText($content);

        $stmt = $conn->prepare("INSERT INTO comments (post_id, content, username) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $post_id, $formatted_content, $_SESSION['username']);
        $stmt->execute();
        logAction("Thêm bình luận vào bài viết ID: $post_id bởi người dùng: {$_SESSION['username']}");
    }

    // Chuyển hướng sau khi bình luận để ngăn việc gửi form lặp lại
    header("Location: index.php");
    exit();
}

// Xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $comment_id, $_SESSION['username']);
    $stmt->execute();
    logAction("Xóa bình luận ID: $comment_id bởi người dùng: {$_SESSION['username']}");

    // Chuyển hướng để làm mới trang
    header("Location: index.php");
    exit();
}
?>