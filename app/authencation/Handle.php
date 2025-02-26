<?php
$currentUser = $_SESSION['username'];
$currentRole = $_SESSION['role'] ?? 'member'; 

// Nếu không phải admin, chặn truy cập
$notAdmin = ($currentRole !== 'owner');

// Giới hạn role admin
$limitAdmin = 30;

// Khởi tạo thông báo
$msg = "";


// Xử lý form khi submit
if (!$notAdmin && isset($_POST['submit'])) {
    $name = trim($_POST['name']);
    $action = isset($_POST['action']) ? $_POST['action'] : $_POST['submit'];

    // Không cho user tự thêm chính mình làm Admin
    if ($action === 'add' && $name === $currentUser) {
        $msg = "Không thể tự thêm chính bạn làm Admin.";
    } else {
        // Kiểm tra user có tồn tại không
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $msg = "User không tồn tại trong record.";
        } else {
            $user = $result->fetch_assoc();
            if ($action === 'add') {
                if ($user['role'] === 'admin') {
                    $msg = "User đã là Admin rồi.";
                } else {
                    $stmtCount = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE role = 'admin'");
                    $stmtCount->execute();
                    $resultCount = $stmtCount->get_result();
                    $rowCount = $resultCount->fetch_assoc();
                    $count = $rowCount['cnt'];

                    if ($count >= $limitAdmin) {
                        $msg = "Đã đạt giới hạn số lượng Admin.";
                    } else {
                        $stmtUpdate = $conn->prepare("UPDATE users SET role = 'admin' WHERE username = ?");
                        $stmtUpdate->bind_param("s", $name);
                        if ($stmtUpdate->execute()) {
                            $msg = "Cập nhật role thành công cho user: " . htmlspecialchars($name);
                        } else {
                            $msg = "Lỗi khi cập nhật role.";
                        }
                        $stmtUpdate->close();
                    }
                    $stmtCount->close();
                }
            } elseif ($action === 'confirm_remove') {
                if ($user['role'] !== 'admin') {
                    $msg = "User không phải là Admin.";
                } else {
                    $stmtUpdate = $conn->prepare("UPDATE users SET role = 'member' WHERE username = ?");
                    $stmtUpdate->bind_param("s", $name);
                    if ($stmtUpdate->execute()) {
                        $msg = "Đã xóa quyền Admin của user: " . htmlspecialchars($name);
                    } else {
                        $msg = "Lỗi khi cập nhật role.";
                    }
                    $stmtUpdate->close();
                }
            }
        }
        $stmt->close();
    }
}
?>