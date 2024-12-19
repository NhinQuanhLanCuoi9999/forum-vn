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
$ip = isset($_GET['ip']) ? $_GET['ip'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : null;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

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
