<?php
$posts_per_page = 7;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $posts_per_page;

// Đếm tổng số bài đăng
$total_posts_query = "SELECT COUNT(*) as total FROM posts";
$total_posts_result = mysqli_query($conn, $total_posts_query);
$total_posts_row = mysqli_fetch_assoc($total_posts_result);
$total_posts = $total_posts_row['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Lấy bài đăng theo LIMIT
$query = "SELECT * FROM posts ORDER BY created_at DESC LIMIT $offset, $posts_per_page";
$result = mysqli_query($conn, $query);
?>