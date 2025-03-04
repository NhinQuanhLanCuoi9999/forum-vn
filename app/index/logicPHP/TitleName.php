<?php
// Chuẩn bị truy vấn lấy cả title và name trong một lần
$sql = "SELECT title, name FROM misc LIMIT 1";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Lỗi SQL: " . $conn->error);
    header("Location: error.php");
    exit();
}

// Thực thi truy vấn
$stmt->execute();
$stmt->bind_result($page_title, $forum_name);
$stmt->fetch();
$stmt->close();

// Kiểm tra giá trị NULL và chống XSS
$page_title = htmlspecialchars($page_title ?? '', ENT_QUOTES, 'UTF-8');
$forum_name = htmlspecialchars($forum_name ?? '', ENT_QUOTES, 'UTF-8');
?>
