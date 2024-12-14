<?php
// Truy vấn để lấy thông tin người dùng từ cơ sở dữ liệu
$query = "SELECT id, created_at FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $createdAt = $user['created_at'];
} else {
    // Xử lý nếu không tìm thấy người dùng
    $userId = null;
    $createdAt = null;
}

// Lấy tên người dùng từ session
$username = $_SESSION['username']; // Giả sử bạn lưu tên người dùng trong session

// Truy vấn để lấy thông tin người dùng từ cơ sở dữ liệu
$query = "SELECT id, created_at, `desc` FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $createdAt = $user['created_at'];
    $userDesc = $user['desc']; // Lấy mô tả người dùng
} else {
    // Xử lý nếu không tìm thấy người dùng
    $userId = null;
    $createdAt = null;
    $userDesc = ''; // Nếu không có mô tả, trả về chuỗi rỗng
}

// Cập nhật mô tả người dùng khi gửi form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newDesc = $_POST['desc'];
    
    // Cập nhật mô tả vào cơ sở dữ liệu
    $updateQuery = "UPDATE users SET `desc` = ? WHERE username = ?";
    $stmt_update = $conn->prepare($updateQuery);
    $stmt_update->bind_param("ss", $newDesc, $username);
    $stmt_update->execute();

    // Cập nhật lại mô tả trong biến
    $userDesc = $newDesc;
}
?>