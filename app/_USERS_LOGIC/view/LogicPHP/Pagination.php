<?php
// Số lượng bình luận hiển thị trên mỗi trang
$commentsPerPage = 5;

// Lấy số trang hiện tại (mặc định là 1 nếu không có)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$startFrom = ($page - 1) * $commentsPerPage;

// Lấy ID bài viết
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Tính tổng số bình luận
$result = $conn->query("SELECT COUNT(*) AS total_comments FROM comments WHERE post_id = $postId");
$row = $result->fetch_assoc();
$totalComments = $row['total_comments'];

// Tính tổng số trang
$totalPages = ceil($totalComments / $commentsPerPage);

// Lấy các bình luận cho trang hiện tại
$commentsQuery = $conn->query("SELECT * FROM comments WHERE post_id = $postId ORDER BY created_at DESC LIMIT $startFrom, $commentsPerPage");

// Lấy bài viết từ cơ sở dữ liệu
$postQuery = $conn->query("SELECT * FROM posts WHERE id = $postId");
$post = $postQuery->fetch_assoc();

// Kiểm tra người dùng đã đăng nhập hay chưa
$isLoggedIn = isset($_SESSION['username']);
$isOwner = $isLoggedIn && isset($post['username']) && $_SESSION['username'] === $post['username'];

?>