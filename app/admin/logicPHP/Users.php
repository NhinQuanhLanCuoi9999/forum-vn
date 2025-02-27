<?php

// Kiểm tra role từ session
$session_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Xóa người dùng
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    // Lấy thông tin tài khoản để kiểm tra role
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Kiểm tra nếu user bị xóa là admin hoặc owner mà session không phải owner thì chặn
        if (($user['role'] === 'admin' || $user['role'] === 'owner') && $session_role !== 'owner') {
            $_SESSION['ss1'] = '<div class="alert alert-danger">Bạn không có quyền xóa tài khoản quản trị.</div>';
        } else {
            echo "<script>
                    if(confirm('Bạn có chắc chắn muốn xóa tài khoản này không?')) {
                        window.location.href = 'admin_tool/admin.php?confirm_delete_user=$user_id';
                    }
                  </script>";
        }
    } else {
        $_SESSION['ss1'] = '<p style="color: red;">Người dùng không tồn tại.</p>';
    }
}

// Xác nhận xóa người dùng
if (isset($_GET['confirm_delete_user'])) {
    $user_id = $_GET['confirm_delete_user'];
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Kiểm tra quyền
    if (($user['role'] === 'admin' || $user['role'] === 'owner') && $session_role !== 'owner') {
        $_SESSION['ss1'] = '<div class="alert alert-danger">Bạn không có quyền xóa tài khoản quản trị.</div>';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();

        logAction("Đã xóa tài khoản '{$user['username']}' thành công.");
        $_SESSION['ss1'] = "<div class='alert alert-success'>Đã xóa tài khoản '{$user['username']}' thành công.</div>";
        header("Location: admin_tool/admin.php?section=users");
        exit();
    }
}

// Chỉnh sửa tên người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $new_username = $_POST['new_username'];

    // Kiểm tra thông tin user
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Nếu user bị đổi tên là admin hoặc owner mà session không phải owner thì chặn
    if (($user['role'] === 'admin' || $user['role'] === 'owner') && $session_role !== 'owner') {
        $_SESSION['ss1'] = '<div class="alert alert-danger">Bạn không có quyền chỉnh sửa tài khoản quản trị.</div>';
    } elseif (empty($new_username)) {
        $_SESSION['ss1'] = '<div class="alert alert-danger">Tên người dùng không được để trống.</div>';
    } elseif (strlen($new_username) < 6) {
        $_SESSION['ss1'] = '<div class="alert alert-danger">Tên người dùng phải có ít nhất 6 ký tự.</div>';
    } elseif ($new_username === 'admin') {
        $_SESSION['ss1'] = '<div class="alert alert-danger">Bạn không thể đổi tên thành tài khoản quản trị.</div>';
    } else {
        // Kiểm tra nếu tên mới đã tồn tại
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $count_result = $stmt->get_result();
        $count = $count_result->fetch_row()[0];

        if ($count > 0) {
            $_SESSION['ss1'] = '<div class="alert alert-danger">Tên đã bị trùng.</div>';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $stmt->bind_param("ss", $new_username, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Lỗi: " . $stmt->error);
                }
                if ($stmt->affected_rows === 0) {
                    throw new Exception("Không có thay đổi nào được thực hiện.");
                }

                // Cập nhật username trong các bảng khác
                $stmt = $conn->prepare("UPDATE posts SET username = ? WHERE username = ?");
                $stmt->bind_param("ss", $new_username, $user['username']);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE comments SET username = ? WHERE username = ?");
                $stmt->bind_param("ss", $new_username, $user['username']);
                $stmt->execute();

                $stmt = $conn->prepare("UPDATE bans SET username = ? WHERE username = ?");
                $stmt->bind_param("ss", $new_username, $user['username']);
                $stmt->execute();

                $_SESSION['ss1'] = "<div class='alert alert-success'>Đã đổi tài khoản '{$user['username']}' thành '$new_username' thành công.</div>";
                logAction("Đã đổi tài khoản '{$user['username']}' thành '$new_username' thành công.");
                header("Location: admin_tool/admin.php?section=users");
                exit();
            } catch (Exception $e) {
                $_SESSION['ss1'] = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
            }
        }
    }
}

?>
