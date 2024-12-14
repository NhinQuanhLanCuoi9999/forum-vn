<?php
// Xóa người dùng
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];

    // Lấy thông tin tài khoản để kiểm tra tên người dùng
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Kiểm tra xem người dùng có phải là admin không
        if ($user['username'] === 'admin') {
            $_SESSION['message'] = '<div class="alert alert-danger">Bạn không thể xóa tài khoản quản trị.</div>';
        } else {
            echo "<script>
                    if(confirm('Bạn có chắc chắn muốn xóa tài khoản này không?')) {
                        window.location.href = 'src/admin.php?confirm_delete_user=$user_id';
                    }
                  </script>";
        }
    } else {
        $_SESSION['message'] = '<p style="color: red;">Người dùng không tồn tại.</p>';
    }
}

// Xác nhận xóa người dùng
if (isset($_GET['confirm_delete_user'])) {
    $user_id = $_GET['confirm_delete_user'];
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Ghi log
    logAction("Đã xóa tài khoản '{$user['username']}' thành công.");
    
    $_SESSION['message'] = "<div class='alert alert-success'>Đã xóa tài khoản '{$user['username']}' thành công.</div>";
    header("Location: src/admin.php?section=users");
    exit();
}

// Chỉnh sửa tên người dùng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $new_username = $_POST['new_username'];

    // Kiểm tra tên người dùng
    if (!empty($new_username)) {
        // Kiểm tra nếu tên mới là "admin"
        if ($new_username === 'admin') {
            $_SESSION['message'] = '<div class="alert alert-danger">Bạn không thể đổi tên thành tài khoản quản trị.</div>';
        } else {
            // Kiểm tra nếu tài khoản admin cố gắng đổi tên
            $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user['username'] === 'admin') {
                $_SESSION['message'] = '<div class="alert alert-danger">Bạn không được phép đổi tên tài khoản admin thành tên khác.</div>';
            } else {
                // Kiểm tra xem tên người dùng đã tồn tại hay chưa
                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $stmt->bind_param("s", $new_username);
                $stmt->execute();
                $count_result = $stmt->get_result();
                $count = $count_result->fetch_row()[0];

                if ($count > 0) {
                    $_SESSION['message'] = '<div class="alert alert-danger">Tên đã bị trùng.</div>';
                } else {
                    // Nếu không có vấn đề gì, tiến hành cập nhật tên người dùng
                    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                    $stmt->bind_param("si", $new_username, $user_id);
                    
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            $_SESSION['message'] = '<div class="alert alert-success">Đổi tên thành công.</div>';
                        } else {
                            $_SESSION['message'] = '<div class="alert alert-warning">Không có thay đổi nào được thực hiện.</div>';
                        }
                    } else {
                        $_SESSION['message'] = '<div class="alert alert-danger">Lỗi: ' . $stmt->error . '</div>';
                    }
                }
            }
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Tên người dùng không được để trống.</div>';
    }

    // Ghi log
    logAction("Đã đổi tài khoản '{$user['username']}' thành '$new_username' thành công.");
    
    $_SESSION['message'] = "<div class='alert alert-success'>Đã đổi tài khoản '{$user['username']}' thành '$new_username' thành công.</div>";
    header("Location: src/admin.php?section=users");
    exit();
}
?>