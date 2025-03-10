<?php
$limit = 5; // Số bài viết trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Số trang hiện tại
$offset = ($page - 1) * $limit; // Tính toán offset cho câu truy vấn SQL

// Fetch tổng số bài viết cho việc tính toán phân trang
if (isset($_GET['section']) && $_GET['section'] === 'posts') {
    // Tính tổng số bài viết
    $total_posts_result = $conn->query("SELECT COUNT(*) as count FROM posts");
    $total_posts = $total_posts_result->fetch_assoc()['count'];
    $total_pages = ceil($total_posts / $limit);

    // Lấy các bài viết theo phân trang
    $posts_query = "SELECT * FROM posts LIMIT $limit OFFSET $offset";
    $posts_result = $conn->query($posts_query);
}
?>