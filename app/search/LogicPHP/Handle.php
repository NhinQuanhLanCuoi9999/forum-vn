<?php
// Lấy các tham số từ form
$search = isset($_GET['search']) ? $_GET['search'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Xây dựng câu lệnh SQL
$query = "SELECT * FROM posts WHERE content LIKE ? ";

if ($start_date && $end_date) {
    $query .= "AND created_at BETWEEN ? AND ? ";
}

?>