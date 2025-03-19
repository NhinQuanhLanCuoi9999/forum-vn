<?php
// Xử lý xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = intval($_GET['delete_comment']);

    // Lấy thông tin bình luận cần xóa (chỉ lấy các trường cần thiết)
    $query_comment = "SELECT comments.id, comments.username, comments.content, users.role AS user_role 
                      FROM comments 
                      JOIN users ON comments.username = users.username 
                      WHERE comments.id = ?";
    $stmt = mysqli_prepare($conn, $query_comment);
    mysqli_stmt_bind_param($stmt, "i", $comment_id);
    mysqli_stmt_execute($stmt);
    $result_comment = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result_comment) > 0) {
        $comment = mysqli_fetch_assoc($result_comment);
        $comment_role = $comment['user_role'];
        $comment_content = $comment['content'];

        if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && $comment_role === 'member')) {
            // Xóa bình luận
            $delete_comment_query = "DELETE FROM comments WHERE id = ?";
            $stmt_delete = mysqli_prepare($conn, $delete_comment_query);
            mysqli_stmt_bind_param($stmt_delete, "i", $comment_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);

            // Ghi log xóa bình luận, bao gồm cả nội dung comment
            writeLog($comment_id, $comment_content, 'bình luận');

            $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Bình luận đã được xóa thành công.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Bạn không có quyền xóa bình luận này!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    // Đóng statement
    mysqli_stmt_close($stmt);

    // Reload trang
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
?>
