<?php
// Lấy tên người dùng từ URL (hoặc có thể lấy từ session nếu đã đăng nhập)
$user = isset($_GET['username']) ? $_GET['username'] : '';

// Truy vấn thông tin người dùng
$sql_user = "SELECT * FROM users WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $user);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

// Kiểm tra nếu người dùng tồn tại
if ($result_user->num_rows > 0) {
    $user_info = $result_user->fetch_assoc();
} else {
    echo "<p>Người dùng không tồn tại.</p>";
    exit;
}

// Truy vấn các bài đăng của người dùng
$sql_posts = "SELECT * FROM posts WHERE username = ? ORDER BY created_at DESC";
$stmt_posts = $conn->prepare($sql_posts);
$stmt_posts->bind_param("s", $user);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();
?>