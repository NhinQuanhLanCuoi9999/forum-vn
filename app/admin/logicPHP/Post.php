<?php
// Xóa bài viết
if (isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    echo "<script>
            if(confirm('Bạn có chắc chắn muốn xóa bài viết này không?')) {
                window.location.href = 'src/admin.php?confirm_delete_post=$post_id';
            }
          </script>";
}

// Xác nhận xóa bài viết
if (isset($_GET['confirm_delete_post'])) {
    $post_id = $_GET['confirm_delete_post'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();

    // Ghi log
    logAction("Đã xóa bài viết với ID '$post_id'.");

    $_SESSION['message'] = "<div class='alert alert-success'>Đã xóa bài viết với ID '$post_id'.</div>";
    header("Location: src/admin.php?section=posts");
    exit();
}
?>