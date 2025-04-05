<?php
// Đặt múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Xử lý thay đổi phân quyền (thêm/hủy admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_permission'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $message = '<div class="alert alert-danger">Lỗi xác thực. Vui lòng thử lại sau.</div>';
    } else {
        if ($session_role !== 'owner') {
            $message = '<div class="alert alert-danger">Bạn không có quyền thay đổi phân quyền.</div>';
        } else {
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (!preg_match('/^[0-9a-fA-F-]{36}$/', $user_id)) {
                $message = '<div class="alert alert-danger">ID người dùng không hợp lệ.</div>';
            } else {
                $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    $message = '<div class="alert alert-danger">Người dùng không tồn tại.</div>';
                } else {
                    $user = $result->fetch_assoc();
                    if ($user['role'] === 'owner') {
                        $message = '<div class="alert alert-danger">Không thể thay đổi quyền của owner.</div>';
                    } else {
                        if ($user['role'] === 'admin') {
                            $new_role = 'member';
                            $action = 'hủy';
                        } else {
                            $new_role = 'admin';
                            $action = 'thêm';
                        }
                        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                        $stmt->bind_param("ss", $new_role, $user_id);
                        $stmt->execute();
                        if ($stmt->affected_rows === 0) {
                            $message = '<div class="alert alert-danger">Không có thay đổi nào.</div>';
                        } else {
                            $message = "<div class='alert alert-success'>Đã $action quyền admin cho tài khoản '{$user['username']}' thành công.</div>";
                            
                            // Lưu log vào /logs/admin/admin-log.txt
                            $log_dir = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
                            $log_file = $log_dir . "admin/admin-log.txt";

                            if (!is_dir($log_dir)) {
                                mkdir($log_dir, 0777, true);
                            }

                            $date = date("d/m/Y | H:i:s");
                            $admin_user = $_SESSION['username'];
                            $ip = $_SERVER['REMOTE_ADDR'];
                            $log_entry = "[$date] $admin_user (IP : $ip) Đã $action quyền admin cho {$user['username']}\n";
                            
                            $fp = fopen($log_file, "a");
                            fwrite($fp, $log_entry);
                            fclose($fp);
                        }
                    }
                }
            }
        }
    }
}
?>
