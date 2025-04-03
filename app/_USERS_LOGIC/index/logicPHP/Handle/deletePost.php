<?php
// Hiển thị thông báo lỗi nếu có
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
}

// Xóa bài viết
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Lấy tên tệp từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT file FROM posts WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $id, $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    
    if ($post) {
        // Xóa tệp tin nếu có
        if ($post['file'] && file_exists('uploads/' . $post['file'])) {
            unlink('uploads/' . $post['file']);
        }

        // Xóa bài viết
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        logAction("Xóa bài viết với ID: $id bởi người dùng: {$_SESSION['username']}");
    } else {
        $_SESSION['error'] = "Bạn không có quyền xóa bài viết này!";
    }

    // Chuyển hướng để làm mới trang
    header("Location: index.php");
    exit();
}
?>