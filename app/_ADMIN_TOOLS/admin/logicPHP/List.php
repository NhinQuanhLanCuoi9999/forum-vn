<?php
// Lấy danh sách người dùng
$users = $conn->query("SELECT * FROM users");

// Lấy danh sách bài viết
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");

// Lấy danh sách bình luận
$comments = [];
$result = $conn->query("SELECT * FROM comments");
while ($comment = $result->fetch_assoc()) {
    $comments[$comment['post_id']][] = $comment; // Nhóm bình luận theo post_id
}

$total_users = $users->num_rows; // Tổng số người dùng
?>