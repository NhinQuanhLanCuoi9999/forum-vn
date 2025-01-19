<?php


// Sử dụng Prepared Statements để tránh SQL Injection
$sql = "SELECT title FROM misc LIMIT 1";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}

// Thực thi truy vấn
$stmt->execute();
$stmt->bind_result($page_title);
$stmt->fetch();
$stmt->close();

// Kiểm tra kết quả truy vấn và sử dụng htmlspecialchars để bảo vệ chống XSS
if (isset($page_title)) {
    $page_title = htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8');
}

// Sử dụng Prepared Statements cho truy vấn thứ hai
$sql = "SELECT name FROM misc LIMIT 1";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Lỗi chuẩn bị truy vấn: " . $conn->error);
}

// Thực thi truy vấn
$stmt->execute();
$stmt->bind_result($forum_name);
$stmt->fetch();
$stmt->close();

// Kiểm tra kết quả và bảo vệ chống XSS
if (isset($forum_name)) {
    $forum_name = htmlspecialchars($forum_name, ENT_QUOTES, 'UTF-8');
}
?>
