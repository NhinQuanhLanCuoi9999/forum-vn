<?php
include_once 'RateLimit.php';
// Xử lý xóa reply nếu được chủ sở hữu yêu cầu
if (isset($_GET['delete_reply'])) {
    $replyId = intval($_GET['delete_reply']);
    $stmt = $conn->prepare("SELECT username FROM replies WHERE id = ?");
    $stmt->bind_param("i", $replyId);
    $stmt->execute();
    $reply = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($reply && isset($_SESSION['username']) && $_SESSION['username'] === $reply['username']) {
        $stmt = $conn->prepare("DELETE FROM replies WHERE id = ?");
        $stmt->bind_param("i", $replyId);
        $stmt->execute();
        $stmt->close();
    }
    
    $postId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    header("Location: view.php?id=" . $postId);
    exit;
}

// Xử lý khi gửi reply (trả lời bình luận)
if (isset($_POST['submit_reply'])) {
    $postId = intval($_GET['id']); // Lấy ID bài viết để redirect

    // Gọi hàm kiểm tra rate limit
    checkRateLimit($postId);

    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'CSRF token không hợp lệ, đừng hack nhé!';
        header("Location: view.php?id=" . $postId);
        exit;
    }
    // Regenerate token mới sau mỗi request
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    $comment_id = intval($_POST['comment_id']);
    $reply_content = htmlspecialchars(trim($_POST['reply_content']), ENT_QUOTES, 'UTF-8');
    $username = $_SESSION['username'];

    if (empty($reply_content)) {
        $_SESSION['error'] = 'Nội dung trả lời không được để trống đâu, viết chút đi!';
        header("Location: view.php?id=" . $postId);
        exit;
    }

    // Kiểm tra xem comment có tồn tại không
    $stmt = $conn->prepare("SELECT id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_assoc()) {
        $_SESSION['error'] = 'Bình luận không tồn tại!';
        header("Location: view.php?id=" . $postId);
        exit;
    }
    $stmt->close();

    // Insert reply vào bảng replies
    $stmt = $conn->prepare("INSERT INTO replies (comment_id, content, username) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $comment_id, $reply_content, $username);
    $stmt->execute();
    $stmt->close();

    header("Location: view.php?id=" . $postId);
    exit;
}
?>