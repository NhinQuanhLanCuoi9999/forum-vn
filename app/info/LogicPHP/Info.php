<?php
$username = $_SESSION['username'];

// Lấy thông tin người dùng (bao gồm description) từ cơ sở dữ liệu
$query = "SELECT id, created_at, description FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $createdAt = $user['created_at'];
    // Nếu khóa description không tồn tại hoặc là null, gán chuỗi rỗng
    $userDesc = isset($user['description']) && $user['description'] !== null ? $user['description'] : '';
} else {
    // Xử lý nếu không tìm thấy người dùng
    $userId = null;
    $createdAt = null;
    $userDesc = '';
}

// Cập nhật mô tả người dùng khi gửi form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Lưu ý: trường textarea có name="desc"
    $newDesc = isset($_POST['desc']) ? $_POST['desc'] : '';
    
    // Cập nhật mô tả vào cơ sở dữ liệu
    $updateQuery = "UPDATE users SET description = ? WHERE username = ?";
    $stmt_update = $conn->prepare($updateQuery);
    $stmt_update->bind_param("ss", $newDesc, $username);
    $stmt_update->execute();

    // Cập nhật lại mô tả trong biến
    $userDesc = $newDesc;
}




// Truy vấn để lấy id của người dùng
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Truy vấn để lấy id của người dùng
$sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userId = $row['id']; // Đã có kiểm tra trước khi dùng
    
}

?>