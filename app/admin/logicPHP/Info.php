<?php
// Lấy tổng số người dùng
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0]; 

// Lấy tổng số bài viết
$total_posts = $conn->query("SELECT COUNT(*) FROM posts")->fetch_row()[0]; 

// Lấy tổng số bình luận
$total_comments = $conn->query("SELECT COUNT(*) FROM comments")->fetch_row()[0]; 

// Lấy tổng số người dùng bị cấm
$total_bans = $conn->query("SELECT COUNT(*) FROM bans")->fetch_row()[0]; 
?>