<?php
// Thiết lập số bản ghi mỗi trang
$limit = 5;

// Lấy số trang từ query string, mặc định là trang 1 nếu không có tham số
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Đảm bảo trang không nhỏ hơn 1
$offset = ($page - 1) * $limit;

// Truy vấn tổng số bản ghi để tính toán tổng số trang
$totalQuery = "SELECT COUNT(*) FROM api_keys";
$totalResult = $conn->query($totalQuery);
$totalRows = $totalResult->fetch_row()[0];

// Tính toán tổng số trang
$totalPages = ceil($totalRows / $limit);

// Truy vấn lấy các bản ghi theo phân trang với sắp xếp theo id giảm dần
$query = "SELECT * FROM api_keys ORDER BY id DESC LIMIT $limit OFFSET $offset";
$apiKeysResult = $conn->query($query);

// Chuyển đổi kết quả truy vấn thành mảng
$apiKeys = [];
while ($row = $apiKeysResult->fetch_assoc()) {
    $apiKeys[] = $row;
}

?>