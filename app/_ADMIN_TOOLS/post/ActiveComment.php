<?php

// Xử lý cập nhật trạng thái (Vô hiệu hóa/Kích hoạt)
if (isset($_GET['toggle_status'])) {
    $id = intval($_GET['toggle_status']);

    // Lấy trạng thái hiện tại của bài đăng
    $stmt = $conn->prepare("SELECT status FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $currentStatus = $row['status'] ?? 0;
    $stmt->close();

    // Nếu status = 2 thì set lại 0 (kích hoạt), ngược lại thì set 2 (vô hiệu hóa)
    $newStatus = ($currentStatus == '2') ? '0' : '2';

    $stmt = $conn->prepare("UPDATE posts SET status = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $newStatus, $id);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
        $stmt->close();
    }
}
?>