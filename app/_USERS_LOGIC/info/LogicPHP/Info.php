<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? null;
$userId = null;
$createdAt = null;
$userDesc = '';
$userRole = 'Không xác định';
$lastLogin = null; 
$isVerified = false;

if ($username) {
    $query = "SELECT id, created_at, role, description, last_login, is_active FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userId = $row['id'] ?? null;
        $createdAt = $row['created_at'] ?? null;
        $userDesc = $row['description'] ?? '';
        $userRole = $row['role'] ?? 'Không xác định';
        $lastLogin = $row['last_login'] ?? null; // Lấy giá trị last_login từ DB
        $isVerified = ($row['is_active'] == 1); // Gán giá trị xác minh từ DB
    }
    $stmt->close();
}

// Đưa vào session để đảm bảo biến này tồn tại trên toàn trang
$_SESSION['isVerified'] = $isVerified;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['desc'])) {
    $newDesc = trim($_POST['desc']);
    $stmt = $conn->prepare("UPDATE users SET description = ? WHERE username = ?");
    $stmt->bind_param("ss", $newDesc, $username);
    if ($stmt->execute()) {
        // Reload lại trang sau khi cập nhật
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
    $stmt->close();
}
?>
