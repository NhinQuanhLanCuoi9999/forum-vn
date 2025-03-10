<?php


// Xử lý DELETE user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $message = '<div class="alert alert-danger">Lỗi xác thực. Vui lòng thử lại sau.</div>';
    } else {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $user_id)) {
            $message = '<div class="alert alert-danger">ID người dùng không hợp lệ.</div>';
        } else {
            $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Kiểm tra nếu user cần xóa là admin/owner
                if ($user['role'] === 'admin' || $user['role'] === 'owner') {
                    if ($session_role === 'admin' || ($session_role === 'owner' && $user['role'] === 'owner')) {
                        $message = '<div class="alert alert-danger">Bạn không có quyền xóa tài khoản này.</div>';
                    } else {
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->bind_param("s", $user_id);
                        $stmt->execute();
                        logAction("Đã xóa tài khoản '{$user['username']}' thành công.");
                        $message = "<div class='alert alert-success'>Đã xóa tài khoản '{$user['username']}' thành công.</div>";
                    }
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("s", $user_id);
                    $stmt->execute();
                    logAction("Đã xóa tài khoản '{$user['username']}' thành công.");
                    $message = "<div class='alert alert-success'>Đã xóa tài khoản '{$user['username']}' thành công.</div>";
                }
            } else {
                $message = '<div class="alert alert-danger">Người dùng không tồn tại.</div>';
            }
        }
    }
}
?>