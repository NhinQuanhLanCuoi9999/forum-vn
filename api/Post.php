<?php
session_start();
header('Content-Type: application/json');

include '../config.php';

// Kiểm tra API key
if (!isset($_GET['api'])) {
    http_response_code(400);
    echo json_encode(["error" => "API key is required."]);
    exit;
}

$api_key = $_GET['api'];

// Xác thực API key
$stmt = $conn->prepare("SELECT id FROM api_keys WHERE api_key = ? AND is_active = 1");
$stmt->bind_param("s", $api_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid or inactive API key."]);
    exit;
}

// Tham số URL
$username = isset($_GET['username']) ? $_GET['username'] : null;
$description = isset($_GET['description']) ? $_GET['description'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at:desc'; // Mặc định sắp xếp theo 'created_at' tăng dần
$limit = 50; // Mặc định trả về tối đa 50 bài viết

// Bắt đầu query
$sql = "SELECT id, username, content, description, created_at FROM posts WHERE 1=1";

// Lọc theo username
if ($username) {
    $sql .= " AND username = ?";
}

// Lọc theo description
if ($description) {
    $sql .= " AND description LIKE ?";
}

// Sắp xếp
$allowed_sort_columns = ['created_at', 'username', 'id'];
$sort_parts = explode(':', $sort);
$sort_column = in_array($sort_parts[0], $allowed_sort_columns) ? $sort_parts[0] : 'created_at';
$sort_order = isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc' ? 'DESC' : 'ASC';
$sql .= " ORDER BY $sort_column $sort_order";

// Giới hạn kết quả
$sql .= " LIMIT ?";

$stmt = $conn->prepare($sql);

// Gắn tham số vào câu lệnh SQL
$params = [];
$param_types = '';
if ($username) {
    $param_types .= 's';
    $params[] = &$username;
}
if ($description) {
    $param_types .= 's';
    $like_description = '%' . $description . '%';
    $params[] = &$like_description;
}
$param_types .= 'i';
$params[] = &$limit;

call_user_func_array([$stmt, 'bind_param'], array_merge([$param_types], $params));

$stmt->execute();
$result = $stmt->get_result();

$posts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

$conn->close();
echo json_encode($posts);
?>