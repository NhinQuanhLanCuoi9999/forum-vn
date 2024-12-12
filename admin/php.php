<?php
// Kiểm tra nếu người dùng đã đăng nhập thông qua cookie
if (isset($_COOKIE['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}

// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Đăng xuất
if (isset($_GET['logout'])) {
    setcookie("username", "", time() - 3600, "/"); // Xóa cookie
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Ghi log vào tệp
function logAction($action) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $log_entry = date('Y-m-d H:i:s') . " - $ip_address: $action\n";
    file_put_contents('logs/admin-log.txt', $log_entry, FILE_APPEND);
}

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
                        window.location.href = 'admin.php?confirm_delete_user=$user_id';
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
    header("Location: admin.php?section=users");
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
    header("Location: admin.php?section=users");
    exit();
}

// Xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    echo "<script>
            if(confirm('Bạn có chắc chắn muốn xóa bình luận này không?')) {
                window.location.href = 'admin.php?confirm_delete_comment=$comment_id';
            }
          </script>";
}

// Xác nhận xóa bình luận
if (isset($_GET['confirm_delete_comment'])) {
    $comment_id = $_GET['confirm_delete_comment'];
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();

    // Ghi log
    logAction("Đã xóa bình luận với ID '$comment_id'.");

    $_SESSION['message'] = "<div class='alert alert-success'>Đã xóa bình luận với ID '$comment_id'.</div>";
    header("Location: admin.php?section=posts");
    exit();
}

// Xóa bài viết
if (isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    echo "<script>
            if(confirm('Bạn có chắc chắn muốn xóa bài viết này không?')) {
                window.location.href = 'admin.php?confirm_delete_post=$post_id';
            }
          </script>";
}

// Xác nhận xóa bài viết
if (isset($_GET['confirm_delete_post'])) {
    $post_id = $_GET['confirm_delete_post'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();

    // Ghi log
    logAction("Đã xóa bài viết với ID '$post_id'.");

    $_SESSION['message'] = "<div class='alert alert-success'>Đã xóa bài viết với ID '$post_id'.</div>";
    header("Location: admin.php?section=posts");
    exit();
}

// Lấy danh sách người dùng
$users = $conn->query("SELECT * FROM users");

// Lấy danh sách bài viết
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");

// Lấy danh sách bình luận
$comments = [];
$result = $conn->query("SELECT * FROM comments");
while ($comment = $result->fetch_assoc()) {
    $comments[$comment['post_id']][] = $comment; // Nhóm bình luận theo post_id
}

$total_users = $users->num_rows; // Tổng số người dùng

// Tìm kiếm bài viết hoặc người dùng
$search_results = [];
if (isset($_GET['section'])) {
    if ($_GET['section'] === 'posts' && isset($_GET['search'])) {
        $search_term = "%" . $conn->real_escape_string($_GET['search']) . "%";
        $stmt = $conn->prepare("SELECT * FROM posts WHERE content LIKE ?");
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $search_results = $stmt->get_result();
    } elseif ($_GET['section'] === 'users' && isset($_GET['search'])) {
        $search_term = "%" . $conn->real_escape_string($_GET['search']) . "%";
        $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ?");
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $search_results = $stmt->get_result();
    }
}

// Hiển thị thông báo nếu có
if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']); // Xóa thông báo sau khi đã hiển thị
}

// Lấy tổng số người dùng
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0]; 

// Lấy tổng số bài viết
$total_posts = $conn->query("SELECT COUNT(*) FROM posts")->fetch_row()[0]; 

// Lấy tổng số bình luận
$total_comments = $conn->query("SELECT COUNT(*) FROM comments")->fetch_row()[0]; 

// Lấy tổng số người dùng bị cấm
$total_bans = $conn->query("SELECT COUNT(*) FROM bans")->fetch_row()[0]; 
?>
