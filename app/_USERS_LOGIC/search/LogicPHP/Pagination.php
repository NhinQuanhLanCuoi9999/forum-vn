<?php
// Kết nối cơ sở dữ liệu
include('../config.php');

// Tính phân trang
$per_page = 5; // Số bài đăng mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_limit = ($page - 1) * $per_page;

// Tạo câu lệnh SQL cho việc lấy bài đăng với phân trang
$query = "SELECT * FROM posts WHERE content LIKE ? "; // Đảm bảo $query có câu lệnh SQL ban đầu
if ($start_date && $end_date) {
    $query .= "AND created_at BETWEEN ? AND ? "; // Nếu có ngày bắt đầu và kết thúc
}
$query .= "LIMIT ?, ?"; // Thêm phân trang vào câu lệnh SQL

$stmt = $conn->prepare($query);

// Gán giá trị cho các tham số trong câu lệnh SQL
$search_param = "%$search%"; // Tạo giá trị tìm kiếm

// Kiểm tra nếu có ngày bắt đầu và kết thúc
if ($start_date && $end_date) {
    // Nếu có ngày bắt đầu và kết thúc, dùng 5 tham số
    $stmt->bind_param('ssssi', $search_param, $start_date, $end_date, $start_limit, $per_page);
} else {
    // Nếu không có ngày bắt đầu và kết thúc, chỉ dùng 3 tham số
    $stmt->bind_param('sii', $search_param, $start_limit, $per_page);
}

$stmt->execute();
$result = $stmt->get_result();
// Lấy tổng số bài đăng để tính phân trang
$count_query = "SELECT COUNT(*) AS total_posts FROM posts WHERE content LIKE ? ";
if ($start_date && $end_date) {
    $count_query .= "AND created_at BETWEEN ? AND ?"; // Nếu có ngày bắt đầu và kết thúc
}
$count_stmt = $conn->prepare($count_query);
if ($start_date && $end_date) {
    // Nếu có ngày bắt đầu và kết thúc
    $count_stmt->bind_param('sss', $search_param, $start_date, $end_date);
} else {
    // Nếu không có ngày bắt đầu và kết thúc
    $count_stmt->bind_param('s', $search_param);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_posts = $count_result->fetch_assoc()['total_posts'];
$total_pages = ceil($total_posts / $per_page);

?>