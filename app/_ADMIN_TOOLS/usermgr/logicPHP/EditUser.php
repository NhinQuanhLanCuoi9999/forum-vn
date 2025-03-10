<?php
// Xử lý EDIT user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
        $message = '<div class="alert alert-danger">Lỗi xác thực. Vui lòng thử lại sau.</div>';
    } else {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!preg_match('/^[0-9a-fA-F-]{36}$/', $user_id)) {
            $message = '<div class="alert alert-danger">ID người dùng không hợp lệ.</div>';
        } else {
            $new_username = filter_input(INPUT_POST, 'new_username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $new_username = trim($new_username);
            if (!preg_match('/^[a-zA-Z0-9]+$/', $new_username)) {
                $message = '<div class="alert alert-danger">Tên người dùng chỉ được chứa chữ cái và số.</div>';
            } else {
                $stmt = $conn->prepare("SELECT username, role FROM users WHERE id = ?");
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    $message = '<div class="alert alert-danger">Người dùng không tồn tại.</div>';
                } else {
                    $user = $result->fetch_assoc();
                    if ($user['role'] === 'admin' || $user['role'] === 'owner') {
                        if ($session_role === 'admin' || ($session_role === 'owner' && $user['role'] === 'owner')) {
                            $message = '<div class="alert alert-danger">Bạn không có quyền chỉnh sửa tài khoản này.</div>';
                        } else {
                            // Kiểm tra tên người dùng hợp lệ
                            if (empty($new_username) || strlen($new_username) < 6 || strtolower($new_username) === 'admin') {
                                $message = '<div class="alert alert-danger">Tên người dùng không hợp lệ.</div>';
                            } else {
                                $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                                $stmt->bind_param("s", $new_username);
                                $stmt->execute();
                                $count_result = $stmt->get_result();
                                $count = $count_result->fetch_row()[0];

                                if ($count > 0) {
                                    $message = '<div class="alert alert-danger">Tên đã bị trùng.</div>';
                                } else {
                                    try {
                                        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                                        $stmt->bind_param("ss", $new_username, $user_id);
                                        $stmt->execute();

                                        if ($stmt->affected_rows === 0) {
                                            throw new Exception("Không có thay đổi nào được thực hiện.");
                                        }

                                        $tables = ['posts', 'comments', 'bans'];
                                        foreach ($tables as $table) {
                                            $stmt = $conn->prepare("UPDATE $table SET username = ? WHERE username = ?");
                                            $stmt->bind_param("ss", $new_username, $user['username']);
                                            $stmt->execute();
                                        }
                                        $message = "<div class='alert alert-success'>Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.</div>";
                                        logAction("Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.");
                                    } catch (Exception $e) {
                                        $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
                                    }
                                }
                            }
                        }
                    } else {
                        // Nếu user không phải admin/owner
                        if (empty($new_username) || strlen($new_username) < 6 || strtolower($new_username) === 'admin') {
                            $message = '<div class="alert alert-danger">Tên người dùng không hợp lệ.</div>';
                        } else {
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                            $stmt->bind_param("s", $new_username);
                            $stmt->execute();
                            $count_result = $stmt->get_result();
                            $count = $count_result->fetch_row()[0];

                            if ($count > 0) {
                                $message = '<div class="alert alert-danger">Tên đã bị trùng.</div>';
                            } else {
                                try {
                                    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                                    $stmt->bind_param("ss", $new_username, $user_id);
                                    $stmt->execute();

                                    if ($stmt->affected_rows === 0) {
                                        throw new Exception("Không có thay đổi nào được thực hiện.");
                                    }

                                    $tables = ['posts', 'comments', 'bans'];
                                    foreach ($tables as $table) {
                                        $stmt = $conn->prepare("UPDATE $table SET username = ? WHERE username = ?");
                                        $stmt->bind_param("ss", $new_username, $user['username']);
                                        $stmt->execute();
                                    }
                                    $message = "<div class='alert alert-success'>Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.</div>";
                                    logAction("Đã đổi tên tài khoản '{$user['username']}' thành '$new_username' thành công.");
                                } catch (Exception $e) {
                                    $message = '<div class="alert alert-danger">' . $e->getMessage() . '</div>';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>