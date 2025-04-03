<?php

// Xử lý react (like/dislike)
if (isset($_POST['reaction']) && isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['username'])) {
        $_SESSION['error'] = "Bạn phải đăng nhập mới có thể phản hồi.";
        header("Location: view.php?id=" . intval($_GET['id']));
        exit;
    }
    $postId = intval($_GET['id']);
    $username = $_SESSION['username'];
    $reaction = $_POST['reaction'] === 'like' ? 'like' : 'dislike';

    // Kiểm tra xem user đã có phản ứng với post này chưa
    $stmt = $conn->prepare("SELECT reaction FROM react WHERE post_id = ? AND username = ?");
    $stmt->bind_param("is", $postId, $username);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->bind_result($currentReaction);
    $stmt->fetch();
    $stmt->close();

    if ($exists) {
        // Nếu phản ứng hiện tại giống phản ứng gửi lên thì ta xóa (toggle)
        if ($currentReaction === $reaction) {
            $stmt = $conn->prepare("DELETE FROM react WHERE post_id = ? AND username = ?");
            $stmt->bind_param("is", $postId, $username);
            $stmt->execute();
            $stmt->close();
        } else {
            // Nếu khác nhau, cập nhật lại phản ứng
            $stmt = $conn->prepare("UPDATE react SET reaction = ?, created_at = CURRENT_TIMESTAMP WHERE post_id = ? AND username = ?");
            $stmt->bind_param("sis", $reaction, $postId, $username);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        // Nếu chưa có, thêm mới
        $stmt = $conn->prepare("INSERT INTO react (post_id, username, reaction) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $postId, $username, $reaction);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: view.php?id=" . $postId);
    exit;
}

// Lấy tổng số like và dislike của bài viết
$postId = intval($_GET['id']);
$result = $conn->query("SELECT 
    SUM(CASE WHEN reaction = 'like' THEN 1 ELSE 0 END) AS total_likes,
    SUM(CASE WHEN reaction = 'dislike' THEN 1 ELSE 0 END) AS total_dislikes
    FROM react WHERE post_id = $postId");
$reactCount = $result->fetch_assoc();
$totalLikes = !empty($reactCount['total_likes']) ? $reactCount['total_likes'] : 0;
$totalDislikes = !empty($reactCount['total_dislikes']) ? $reactCount['total_dislikes'] : 0;

?>