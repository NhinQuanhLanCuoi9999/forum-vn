<?php

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $_SESSION['role'] = $row['role']; // Lưu role vào session
    }


   // Kiểm tra nếu role là admin hoặc owner thì hiển thị nút Admin Panel
if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'owner')) {
    echo '<a href="admin_tool/admin.php" class="admin-button">Admin Panel</a>';
}

}
?>
