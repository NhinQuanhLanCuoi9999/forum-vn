<?php
// Kiểm tra trạng thái đăng nhập
if (!isset($_SESSION['username'])) {
    $userLoggedIn = false;
    $isVerified = false;
    $postStatus = null;
} else {
    $userLoggedIn = true;

    // Kiểm tra trạng thái xác minh của tài khoản
    $stmt = $conn->prepare("SELECT is_active FROM users WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $isVerified = ($result && $result['is_active'] == 1);

    // Lấy ID từ URL
    $postId = $_GET['id'] ?? null;

    if ($postId) {
        // Lấy status của bài post từ bảng posts dựa vào ID
        $stmt = $conn->prepare("SELECT status FROM posts WHERE id = ?");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Gán giá trị status (nếu có)
        $postStatus = $result['status'] ?? null;
    } else {
        $postStatus = null; // Không có ID thì không lấy được status
    }
}
?>
