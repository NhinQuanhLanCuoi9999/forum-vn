<?php
// Lấy ID bài post từ GET
$postId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$viewCount = 0;

if ($postId > 0) {
    // Tạo key duy nhất cho session lưu mảng bài viết đã xem
    $viewKey = "viewed_post_" . $postId;

    // Kiểm tra nếu chưa xem trong session thì mới tăng view
    if (!isset($_SESSION['viewed_posts'][$viewKey])) {
        // Tăng số lượt xem bằng prepared statement
        $updateStmt = $conn->prepare("UPDATE posts SET view = view + 1 WHERE id = ?");
        $updateStmt->bind_param("i", $postId);
        $updateStmt->execute();
        $updateStmt->close();

        // Đánh dấu đã xem trong session (lưu vào mảng)
        $_SESSION['viewed_posts'][$viewKey] = true; // Lưu id bài viết và trạng thái đã xem
    }

    // Lấy số lượt xem mới nhất
    $selectStmt = $conn->prepare("SELECT view FROM posts WHERE id = ?");
    $selectStmt->bind_param("i", $postId);
    $selectStmt->execute();
    $selectStmt->bind_result($viewCount);
    $selectStmt->fetch();
    $selectStmt->close();
}
?>