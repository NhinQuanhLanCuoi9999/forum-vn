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

// Lấy dữ liệu từ bảng `users`
$sql = "SELECT id,username,`desc`,created_at FROM users";
$result = $conn->query($sql);

$comment = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $comment[] = $row;
    }
}

$conn->close();
echo json_encode($comment);
?>