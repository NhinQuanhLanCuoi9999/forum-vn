<?php
// Tìm kiếm bài viết hoặc người dùng
$search_results = [];
if (isset($_GET['section'])) {
    if ($_GET['section'] === 'posts' && isset($_GET['search'])) {
        $search_term = "%" . $conn->real_escape_string($_GET['search']) . "%";
        $stmt = $conn->prepare("SELECT * FROM posts WHERE content LIKE ?");
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $search_results = $stmt->get_result();
    } elseif ($_GET['section'] === 'users' && isset($_GET['search'])) {
        $search_term = "%" . $conn->real_escape_string($_GET['search']) . "%";
        $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ?");
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $search_results = $stmt->get_result();
    }
}

// Hiển thị thông báo nếu có
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']); // Xóa thông báo sau khi đã hiển thị
}
?>