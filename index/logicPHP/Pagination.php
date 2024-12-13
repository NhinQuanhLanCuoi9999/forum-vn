<?php
// Lấy danh sách bài viết với phân trang
$posts_per_section = 8;
$total_posts = $conn->query("SELECT COUNT(*) AS count FROM posts")->fetch_assoc()['count'];
$total_sections = ceil($total_posts / $posts_per_section);

// Lấy section hiện tại từ URL
$current_section = isset($_GET['section']) ? (int)$_GET['section'] : 1;
$current_section = max(1, min($current_section, $total_sections)); // Giới hạn trong khoảng

// Tính toán vị trí bắt đầu
$start_index = ($current_section - 1) * $posts_per_section;

// Lấy danh sách bài viết cho section hiện tại
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT $start_index, $posts_per_section");
?>