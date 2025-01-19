<?php
require_once 'Log.php';

if (empty($_SESSION['csrf_token1'])) {
    // Nếu chưa có, tạo một token mới
    $_SESSION['csrf_token1'] = bin2hex(random_bytes(32)); // Tạo một CSRF token ngẫu nhiên
}
// Bảo vệ CSRF
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    // Kiểm tra CSRF token hợp lệ
    if (!isset($_POST['csrf_token1']) || $_POST['csrf_token1'] !== $_SESSION['csrf_token1']) {
        die("Invalid CSRF token.");
    }

    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    // Kiểm tra nếu nội dung bình luận không rỗng và có chứa từ cấm
    if (!empty($content) && containsBadWords($content)) {
        $_SESSION['error'] = "Nội dung không phù hợp, vui lòng kiểm tra lại.";
    } else {
        // Làm sạch và bảo vệ dữ liệu đầu vào (XSS protection)
        $formatted_content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8'); // Chống XSS

        // Kiểm tra nếu post_id là số và content hợp lệ
        if (is_numeric($post_id) && !empty($formatted_content)) {
            $stmt = $conn->prepare("INSERT INTO comments (post_id, content, username) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $post_id, $formatted_content, $_SESSION['username']);
            $stmt->execute();
            logAction("Thêm bình luận vào bài viết ID: $post_id bởi người dùng: {$_SESSION['username']}");
        } else {
            $_SESSION['error'] = "Dữ liệu không hợp lệ.";
        }
    }

    // Chuyển hướng sau khi bình luận để ngăn việc gửi form lặp lại
    header("Location: index.php");
    exit();
}
// Xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];

    // Kiểm tra nếu comment_id là số và xác thực quyền người dùng
    if (is_numeric($comment_id)) {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND username = ?");
        $stmt->bind_param("is", $comment_id, $_SESSION['username']);
        $stmt->execute();
        logAction("Xóa bình luận ID: $comment_id bởi người dùng: {$_SESSION['username']}");
    } else {
        $_SESSION['error'] = "Dữ liệu không hợp lệ.";
    }

    // Chuyển hướng để làm mới trang
    header("Location: index.php");
    exit();
}
?>