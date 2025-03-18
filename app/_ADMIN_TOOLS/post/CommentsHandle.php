<?php
// Xử lý xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = intval($_GET['delete_comment']);

    // Lấy thông tin bình luận cần xóa
    $query_comment = "SELECT comments.*, users.role AS user_role 
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

        if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && $comment_role === 'member')) {
            // Xóa bình luận
            $delete_comment_query = "DELETE FROM comments WHERE id = ?";
            $stmt_delete = mysqli_prepare($conn, $delete_comment_query);
            mysqli_stmt_bind_param($stmt_delete, "i", $comment_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);

            // 🔹 Ghi log xóa bình luận trực tiếp
            $log_dir = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
            $log_file = $log_dir . "admin-log.txt";

            // Tạo thư mục logs nếu chưa có
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0777, true);
            }

            // Lấy thông tin người dùng
            $user_name = $_SESSION['username'] ?? 'Unknown';
            $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            // Định dạng nội dung log
            $log_entry = "[" . date("d/m/Y | H:i:s") . "] Người dùng : [$user_name] (IP: $user_ip) đã thao tác xóa bình luận có ID [$comment_id]\n";

            // Ghi vào file log (ẩn lỗi nếu không ghi được)
            @file_put_contents($log_file, $log_entry, FILE_APPEND);

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
