<?php
// Xử lý xóa phản hồi
if (isset($_GET['delete_reply'])) {
    $reply_id = intval($_GET['delete_reply']);

    // Sử dụng prepared statement để tránh SQL Injection
    $query_reply = "SELECT replies.*, users.role AS user_role 
                    FROM replies 
                    JOIN users ON replies.username = users.username 
                    WHERE replies.id = ?";
    $stmt = mysqli_prepare($conn, $query_reply);
    mysqli_stmt_bind_param($stmt, "i", $reply_id);
    mysqli_stmt_execute($stmt);
    $result_reply = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result_reply) > 0) {
        $reply = mysqli_fetch_assoc($result_reply);
        $reply_role = $reply['user_role'];

        if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && $reply_role === 'member')) {
            // Chuẩn bị truy vấn xóa phản hồi
            $delete_reply_query = "DELETE FROM replies WHERE id = ?";
            $stmt_delete = mysqli_prepare($conn, $delete_reply_query);
            mysqli_stmt_bind_param($stmt_delete, "i", $reply_id);
            mysqli_stmt_execute($stmt_delete);

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
            $log_entry = "[" . date("d/m/Y | H:i:s") . "] Người dùng : [$user_name] (IP: $user_ip) đã thao tác xóa phản hồi có ID [$reply_id]\n";

            // Ghi vào file log
            @file_put_contents($log_file, $log_entry, FILE_APPEND);

            $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                Phản hồi đã được xóa thành công.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                Bạn không có quyền xóa phản hồi này!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

    // Đóng statement
    mysqli_stmt_close($stmt);
    if (isset($stmt_delete)) {
        mysqli_stmt_close($stmt_delete);
    }

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
?>
