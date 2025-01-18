<?php
// Truy vấn để lấy giá trị 'title' từ bảng misc
$sql = "SELECT title FROM misc LIMIT 1"; // Giả sử bảng 'misc' chứa cột 'title'
$result = $conn->query($sql);

// Nếu có kết quả, gán giá trị cho tiêu đề
if ($result->num_rows > 0) {
    // Lấy dữ liệu và thiết lập tiêu đề trang
    $row = $result->fetch_assoc();
    $page_title = $row['title'];
}
// Truy vấn để lấy giá trị 'name' từ bảng misc
$sql = "SELECT name FROM misc LIMIT 1"; // Giả sử bảng 'misc' chứa cột 'name'
$result = $conn->query($sql);

// Nếu có kết quả, gán giá trị cho tên diễn đàn
if ($result->num_rows > 0) {
    // Lấy dữ liệu và thiết lập tên diễn đàn
    $row = $result->fetch_assoc();
    $forum_name = $row['name'];
}
?>