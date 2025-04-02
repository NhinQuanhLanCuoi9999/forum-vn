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
$stmt = $conn->prepare("SELECT id, remaining_uses FROM api_keys WHERE api_key = ? AND is_active = 1");
$stmt->bind_param("s", $api_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(403);
    echo json_encode(["error" => "Invalid or inactive API key."]);
    exit;
}

$api_key_data = $result->fetch_assoc();

// Kiểm tra remaining_uses
if ($api_key_data['remaining_uses'] <= 0) {
    http_response_code(403);
    echo json_encode(["error" => "API key has no remaining uses."]);

    // Xóa bản ghi nếu remaining_uses = 0
    $delete_stmt = $conn->prepare("DELETE FROM api_keys WHERE id = ?");
    $delete_stmt->bind_param("i", $api_key_data['id']);
    $delete_stmt->execute();

    exit;
}

// Kiểm tra và cập nhật `api_count` trong session
$current_time = time();
if (!isset($_SESSION['api_count'])) {
    $_SESSION['api_count'] = 0;
    $_SESSION['api_count_reset_time'] = $current_time;
} else {
    $elapsed_time = $current_time - $_SESSION['api_count_reset_time'];
    if ($elapsed_time > 60) {
        $_SESSION['api_count'] = 0;
        $_SESSION['api_count_reset_time'] = $current_time;
    }
}

$_SESSION['api_count'] += 1;

if ($_SESSION['api_count'] > 10) {
    http_response_code(429);
    echo json_encode(["error" => "Too many requests. Please try again later."]);
    exit;
}

// Trừ 1 vào remaining_uses
$update_stmt = $conn->prepare("UPDATE api_keys SET remaining_uses = remaining_uses - 1 WHERE id = ?");
$update_stmt->bind_param("i", $api_key_data['id']);
$update_stmt->execute();

// Tham số URL
$username = isset($_GET['username']) ? $_GET['username'] : null;
$content = isset($_GET['content']) ? $_GET['content'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id:desc'; // Mặc định sắp xếp theo 'id' tăng dần

// Bắt đầu query
$sql = "SELECT id, username, content, created_at FROM comments WHERE 1=1";

// Lọc theo username
if ($username) {
    $sql .= " AND username = ?";
}

// Lọc theo content
if ($content) {
    $sql .= " AND content LIKE ?";
}

// Sắp xếp
if ($sort) {
    $allowed_sort_columns = ['created_at', 'username', 'id'];
    $sort_parts = explode(':', $sort);
    $sort_column = in_array($sort_parts[0], $allowed_sort_columns) ? $sort_parts[0] : 'created_at';
    $sort_order = isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY $sort_column $sort_order";
} else {
    // Mặc định sắp xếp theo created_at DESC
    $sql .= " ORDER BY created_at DESC";
}

// Giới hạn kết quả mặc định là 50 bản ghi gần nhất
$sql .= " LIMIT 50";

$stmt = $conn->prepare($sql);

// Gắn tham số vào câu lệnh SQL
$params = [];
$param_types = '';
if ($username) {
    $param_types .= 's';
    $params[] = &$username;
}
if ($content) {
    $param_types .= 's';
    $content_param = '%' . $content . '%';
    $params[] = &$content_param;
}

if ($param_types) {
    call_user_func_array([$stmt, 'bind_param'], array_merge([$param_types], $params));
}

$stmt->execute();
$result = $stmt->get_result();

$comment = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $comment[] = $row;
    }
}

$conn->close();
echo json_encode($comment);
?>