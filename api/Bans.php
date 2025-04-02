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

$api_data = $result->fetch_assoc();

// Kiểm tra remaining_uses
if ($api_data['remaining_uses'] <= 0) {
    // Xóa bản ghi nếu remaining_uses = 0
    $delete_stmt = $conn->prepare("DELETE FROM api_keys WHERE id = ?");
    $delete_stmt->bind_param("i", $api_data['id']);
    $delete_stmt->execute();

    http_response_code(403);
    echo json_encode(["error" => "API key has no remaining uses."]);
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

// Trừ 1 vào remaining_uses của API key
$update_stmt = $conn->prepare("UPDATE api_keys SET remaining_uses = remaining_uses - 1 WHERE id = ?");
$update_stmt->bind_param("i", $api_data['id']);
$update_stmt->execute();

// Tham số URL
$username = isset($_GET['username']) ? $_GET['username'] : null;
$ip = isset($_GET['ip']) ? $_GET['ip'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id:desc'; // Mặc định sắp xếp theo 'created_at' giảm dần
$limit = 50; // Mặc định giới hạn là 50 bản ghi mới nhất

// Bắt đầu query
$sql = "SELECT id, username, ip_address, reason, ban_start, ban_end, permanent FROM bans WHERE 1=1";

// Lọc theo username
if ($username) {
    $sql .= " AND username = ?";
}

// Lọc theo ip_address
if ($ip) {
    $sql .= " AND ip_address = ?";
}

// Sắp xếp
if ($sort) {
    $allowed_sort_columns = ['username', 'ban_start', 'ban_end'];
    $sort_parts = explode(':', $sort);
    $sort_column = in_array($sort_parts[0], $allowed_sort_columns) ? $sort_parts[0] : 'ban_start';
    $sort_order = isset($sort_parts[1]) && strtolower($sort_parts[1]) === 'desc' ? 'DESC' : 'ASC';
    $sql .= " ORDER BY $sort_column $sort_order";
}

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
if ($ip) {
    $param_types .= 's';
    $params[] = &$ip;
}
$param_types .= 'i';
$params[] = &$limit;

call_user_func_array([$stmt, 'bind_param'], array_merge([$param_types], $params));

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