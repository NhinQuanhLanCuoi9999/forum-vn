<?php
include_once 'RateLimit.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_comment'])) {

    $postId = intval($_GET['id']); // Lấy ID bài viết để redirect

    // Gọi hàm kiểm tra rate limit (định nghĩa trong RateLimit.php)
    checkRateLimit($postId);

    // Kiểm tra CSRF token (cải tiến: regenerate sau mỗi request)
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<script>
                alert('Invalid CSRF Token');
                window.location.href='view.php?id=$postId';
              </script>";
        exit();
    }
    // Regenerate token mới cho lần request tiếp theo
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Lấy dữ liệu từ form
    $commentId = $_POST['comment_id'];
    $newContent = trim($_POST['comment_content']);

    // Kiểm tra nếu nội dung rỗng hoặc chỉ toàn khoảng trắng
    if (empty($newContent) || preg_match('/^\s*$/', $newContent)) {
        $_SESSION['error'] = "Bình luận không được để trống hoặc chỉ chứa khoảng trắng!";
        header("Location: view.php?id=$postId");
        exit();
    }

    // Kiểm tra độ dài bình luận
    if (strlen($newContent) > 222) {
        $_SESSION['error'] = "Bình luận không được vượt quá 222 ký tự!";
        header("Location: view.php?id=$postId");
        exit();
    }

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
            logEdit($postId, $commentId, $newContent); // Ghi log chỉnh sửa
            $_SESSION['success'] = "Bình luận đã được cập nhật!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi cập nhật bình luận!";
        }
    } else {
        $_SESSION['error'] = "Bạn không có quyền chỉnh sửa bình luận này!";
    }

    header("Location: view.php?id=$postId");
    exit();
}
?>
