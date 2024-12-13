<?php
// Xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    echo "<script>
            if(confirm('Bạn có chắc chắn muốn xóa bình luận này không?')) {
                window.location.href = 'admin.php?confirm_delete_comment=$comment_id';
            }
          </script>";
}

// Xác nhận xóa bình luận
if (isset($_GET['confirm_delete_comment'])) {
    $comment_id = $_GET['confirm_delete_comment'];
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();

    // Ghi log
    logAction("Đã xóa bình luận với ID '$comment_id'.");

    $_SESSION['message'] = "<div class='alert alert-success'>Đã xóa bình luận với ID '$comment_id'.</div>";
    header("Location: admin.php?section=posts");
    exit();
}
?>