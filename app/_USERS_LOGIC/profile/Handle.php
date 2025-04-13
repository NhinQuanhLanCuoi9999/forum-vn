<?php
// =================== CẤU TRÚC TRUY VẤN ===================
// Lấy tên người dùng từ URL
$user = isset($_GET['username']) ? $_GET['username'] : '';

// Truy vấn thông tin người dùng
$sql_user = "SELECT * FROM users WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

// -------- SỬA: Thay vì exit, ta gán biến báo lỗi nếu không có user --------
if ($result_user->num_rows > 0) {
    $user_info = $result_user->fetch_assoc();
} else {
    $error_message = "Người dùng không tồn tại.";
}

// Truy vấn các bài đăng của người dùng
$sql_posts = "SELECT * FROM posts WHERE username = ? ORDER BY created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("s", $user);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();
?>