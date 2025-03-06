<?php

// Kiểm tra xem người dùng có phải là admin hoặc owner không
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'owner')) {
    header("Location: ../index.php");
    exit();
}
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    // Kiểm tra username có tồn tại trong database không
    $stmt = $conn->prepare("SELECT 1 FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Xóa toàn bộ session
        session_unset();
        session_destroy();
        
        // Chuyển hướng về trang đăng nhập
        header("Location: /");
        exit();
    }
    
    $stmt->close();
}
?>