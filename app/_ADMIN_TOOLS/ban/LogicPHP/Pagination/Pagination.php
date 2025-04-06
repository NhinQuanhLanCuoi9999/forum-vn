<?php
// Kiểm tra xem có dữ liệu 'username' không, nếu không thì gán là NULL
$username = isset($_POST['username']) && !empty($_POST['username']) ? $_POST['username'] : null;

// Xác định số bản ghi mỗi trang và trang hiện tại
$perPage = 10; // Số lượng lệnh cấm mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Lấy trang hiện tại từ URL, mặc định là 1

// Tính toán tổng số lệnh cấm
$sql = "SELECT COUNT(*) AS total FROM bans";
$result = $conn->query($sql);
$totalBans = $result->fetch_assoc()['total'];

// Tính tổng số trang
$totalPages = ceil($totalBans / $perPage);

// Giới hạn kết quả cho trang hiện tại
$offset = ($page - 1) * $perPage;
$sql = "SELECT * FROM bans ORDER BY ban_end DESC LIMIT $offset, $perPage";
$bans = $conn->query($sql);

?>