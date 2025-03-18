<?php

// Xử lý xóa bài đăng
if (isset($_GET['delete'])) {
    $post_id = intval($_GET['delete']);

    // Truy vấn để lấy username của tác giả bài đăng (DÙNG PREPARED STATEMENT)
    $query = "SELECT username FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $post = mysqli_fetch_assoc($result);
        $author_username = $post['username'];

        // Truy vấn để lấy role của tác giả bài đăng (DÙNG PREPARED STATEMENT)
        $query = "SELECT role FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $author_username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $postAuthorRole = 'member'; // Mặc định nếu không tìm thấy
        if ($result && mysqli_num_rows($result) > 0) {
            $author = mysqli_fetch_assoc($result);
            $postAuthorRole = $author['role'];
        }

        $userRole = $_SESSION['role'];

        // Kiểm tra quyền xóa
        if ($userRole === 'owner' || ($userRole === 'admin' && $postAuthorRole === 'member')) {
            // Xóa bài đăng (DÙNG PREPARED STATEMENT)
            $delete_query = "DELETE FROM posts WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $post_id);
            mysqli_stmt_execute($stmt);

            // ✅ Thêm ghi log vào đây
            $user_ip = $_SERVER['REMOTE_ADDR'];
            $user_name = $_SESSION['username'] ?? 'Unknown';

            $log_dir = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
            $log_file = $log_dir . "admin-log.txt";

            // Tạo thư mục logs nếu chưa có
            if (!is_dir($log_dir)) {
                mkdir($log_dir, 0777, true);
            }

            // Nội dung log
            $log_entry = "[" . date("d/m/Y | H:i:s") . "] Người dùng : [$user_name] (IP: $user_ip) đã thao tác xóa bài đăng có ID [$post_id]\n";

            // Ghi vào file log
            @file_put_contents($log_file, $log_entry, FILE_APPEND);

            $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Bài đăng đã được xóa thành công.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Bạn không có quyền xóa bài đăng này!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
?>
