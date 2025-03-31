<?php

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$posts_per_page = 10;
$offset = ($page - 1) * $posts_per_page;

// Xây dựng query string giữ cả `search` và `page`
$query_params = [
    'search' => $search_query !== '' ? $search_query : null,
    'page' => $page > 1 ? $page : null
];
$query_string = http_build_query(array_filter($query_params));
$query_string = $query_string ? '?' . $query_string : '';

// Nếu có tìm kiếm, query sẽ dùng LIKE để lọc theo nội dung hoặc mô tả
if ($search_query !== '') {
    $search_term = mysqli_real_escape_string($conn, $search_query);
    $base_sql = "SELECT * FROM posts WHERE content LIKE '%$search_term%' OR description LIKE '%$search_term%' ORDER BY created_at DESC";
} else {
    $base_sql = "SELECT * FROM posts ORDER BY created_at DESC";
}

// Đếm tổng số bài đăng để phân trang
$count_query = "SELECT COUNT(*) as total FROM (" . $base_sql . ") as sub";
$count_result = mysqli_query($conn, $count_query);
$total_posts = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_posts / $posts_per_page);

// Query bài đăng với LIMIT cho phân trang
$sql = $base_sql . " LIMIT $offset, $posts_per_page";
$result = mysqli_query($conn, $sql);
?>