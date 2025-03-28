<?php
// Mảng lưu số người dùng theo từng tháng
$user_data = array_fill(0, 12, 0);

// Truy vấn lấy số người dùng
$query = "SELECT MONTH(created_at) AS month, COUNT(id) AS total_users 
          FROM users 
          WHERE YEAR(created_at) = YEAR(CURDATE()) 
          GROUP BY MONTH(created_at) 
          ORDER BY MONTH(created_at)";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $user_data[$row['month'] - 1] = (int)$row['total_users'];
}
?>