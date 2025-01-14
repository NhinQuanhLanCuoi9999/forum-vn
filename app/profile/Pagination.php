<?php
// Số bài viết hiển thị trên mỗi trang
$posts_per_page = 5;

// Lấy trang hiện tại từ URL, nếu không có thì mặc định là trang 1
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

// Tính toán vị trí bắt đầu của bài viết trong truy vấn
$offset = ($current_page - 1) * $posts_per_page;

// Truy vấn tổng số bài viết
$total_posts_query = "SELECT COUNT(*) AS total FROM posts WHERE username = ?";
$stmt_total_posts = $conn->prepare($total_posts_query);
if (!$stmt_total_posts) {
    die("Prepare failed: " . $conn->error);
}
$stmt_total_posts->bind_param('s', $user_info['username']);
$stmt_total_posts->execute();
$result_total_posts = $stmt_total_posts->get_result();
if (!$result_total_posts) {
    die("Query failed: " . $stmt_total_posts->error);
}
$total_posts = $result_total_posts->fetch_assoc()['total'];
$stmt_total_posts->close();

// Tính toán tổng số trang
$total_pages = ceil($total_posts / $posts_per_page);

// Truy vấn bài viết với giới hạn và offset
$posts_query = "SELECT * FROM posts WHERE username = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt_posts = $conn->prepare($posts_query);
if (!$stmt_posts) {
    die("Prepare failed: " . $conn->error);
}
$stmt_posts->bind_param('sii', $user_info['username'], $posts_per_page, $offset);
$stmt_posts->execute();
$result_posts = $stmt_posts->get_result();
if (!$result_posts) {
    die("Query failed: " . $stmt_posts->error);
}
?>