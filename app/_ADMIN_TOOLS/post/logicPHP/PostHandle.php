<?php
include_once 'Logs.php';


// Xử lý xóa bài đăng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $post_id = intval($_POST['delete']); // Lấy ID bài đăng cần xóa

    // Truy vấn để lấy username và content của bài đăng (DÙNG PREPARED STATEMENT)
    $query = "SELECT username, content FROM posts WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $post_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $post = mysqli_fetch_assoc($result);
        $author_username = $post['username'];
        $post_content = $post['content'];

        // Truy vấn để lấy role của tác giả bài đăng (DÙNG PREPARED STATEMENT)
        $query2 = "SELECT role FROM users WHERE username = ?";
        $stmt2 = mysqli_prepare($conn, $query2);
        mysqli_stmt_bind_param($stmt2, "s", $author_username);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);

        $postAuthorRole = 'member'; // Mặc định nếu không tìm thấy
        if ($result2 && mysqli_num_rows($result2) > 0) {
            $author = mysqli_fetch_assoc($result2);
            $postAuthorRole = $author['role'];
        }
        mysqli_stmt_close($stmt2);

        $userRole = $_SESSION['role'];

        // Kiểm tra quyền xóa
        if ($userRole === 'owner' || ($userRole === 'admin' && $postAuthorRole === 'member')) {
            // Xóa bài đăng (DÙNG PREPARED STATEMENT)
            $delete_query = "DELETE FROM posts WHERE id = ?";
            $stmt3 = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt3, "i", $post_id);
            mysqli_stmt_execute($stmt3);
            mysqli_stmt_close($stmt3);

            // Ghi log, sử dụng hàm writeLog với type là 'bài đăng'
            writeLog($post_id, $post_content, 'bài đăng');

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

    mysqli_stmt_close($stmt);
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

?>
