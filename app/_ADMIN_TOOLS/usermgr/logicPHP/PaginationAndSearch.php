<?php

// Xử lý tìm kiếm & phân trang (vẫn dùng POST)
$search_term = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_submit'])) {
    $search_term = trim($_POST['search']);
}

$limit = 6;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $limit;
$total_pages = 0;

if (!empty($search_term)) {
    $search_param = "%" . $conn->real_escape_string($search_term) . "%";
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE username LIKE ?");
    if (!$count_stmt) {
        die("Lỗi prepare (COUNT search): " . $conn->error);
    }
    $count_stmt->bind_param("s", $search_param);
    if (!$count_stmt->execute()) {
        die("Lỗi execute (COUNT search): " . $count_stmt->error);
    }
    $count_result = $count_stmt->get_result();
    $total_users = $count_result->fetch_assoc()['count'];
    $total_pages = ceil($total_users / $limit);

    // Thêm ORDER BY theo thứ tự: owner, admin, member
    $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ? ORDER BY FIELD(role, 'owner','admin','member') LIMIT ? OFFSET ?");
    if (!$stmt) {
        die("Lỗi prepare (SELECT search): " . $conn->error);
    }
    $stmt->bind_param("sii", $search_param, $limit, $offset);
    if (!$stmt->execute()) {
        die("Lỗi execute (SELECT search): " . $stmt->error);
    }
    $users_result = $stmt->get_result();
} else {
    $total_users_result = $conn->query("SELECT COUNT(*) as count FROM users");
    if (!$total_users_result) {
        die("Lỗi query (COUNT all users): " . $conn->error);
    }
    $total_users = $total_users_result->fetch_assoc()['count'];
    $total_pages = ceil($total_users / $limit);

    // Thêm ORDER BY theo thứ tự: owner, admin, member
    $users_query = "SELECT * FROM users ORDER BY FIELD(role, 'owner','admin','member') LIMIT $limit OFFSET $offset";
    $users_result = $conn->query($users_query);
    if (!$users_result) {
        die("Lỗi query (SELECT all users): " . $conn->error);
    }
}
?>