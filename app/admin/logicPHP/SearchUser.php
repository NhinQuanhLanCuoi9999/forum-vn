<?php
// Bao gồm config.php để sử dụng kết nối
include '../config.php';

// Lấy giá trị tìm kiếm từ URL
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query tìm kiếm người dùng nếu có
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM users WHERE username LIKE ? LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$search_term = "%$search%";
$stmt->bind_param("s", $search_term);
$stmt->execute();
$users_result = $stmt->get_result();
?>
