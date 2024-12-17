<?php
include_once 'badWord.php';
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