<?php
// Xử lý xóa phản hồi
if (isset($_GET['delete_reply'])) {
    $reply_id = intval($_GET['delete_reply']);

    // Sử dụng prepared statement để tránh SQL Injection
    $query_reply = "SELECT replies.id, replies.username, replies.content, users.role AS user_role 
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
        $reply_content = $reply['content']; // Lấy cột content của phản hồi

        if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && $reply_role === 'member')) {
            // Xóa phản hồi sử dụng prepared statement
            $delete_reply_query = "DELETE FROM replies WHERE id = ?";
            $stmt_delete = mysqli_prepare($conn, $delete_reply_query);
            mysqli_stmt_bind_param($stmt_delete, "i", $reply_id);
            mysqli_stmt_execute($stmt_delete);
            mysqli_stmt_close($stmt_delete);

            // Ghi log sử dụng hàm writeLog với type là 'phản hồi'
            writeLog($reply_id, $reply_content, 'phản hồi');

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
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}
?>
