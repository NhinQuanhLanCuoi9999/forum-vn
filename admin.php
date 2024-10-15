<?php
session_start();
include('config.php');

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
    header("Location: admin.php");
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
                    $stmt->execute();

                    // Ghi log
                    logAction("Đã đổi tài khoản '{$user['username']}' thành '$new_username' thành công.");
                    
                    $_SESSION['message'] = "<div class='alert alert-success'>Đã đổi tài khoản '{$user['username']}' thành '$new_username' thành công.</div>";
                    header("Location: admin.php");
                    exit();
                }
            }
        }
    }
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
    header("Location: admin.php");
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
    header("Location: admin.php");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h1, h2 {
            color: #333;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
/* Thông báo thành công */
.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 4px;
}

/* Màu xanh cho thông báo thành công */
.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

/* Màu đỏ cho thông báo lỗi */
.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
        .user, .post, .comment {
            background: #fff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            max-height: 170px;
            overflow-y: auto;
        }
.alert {
    padding: 15px;  /* Tăng padding cho bảng thông báo */
    margin: 10px 0;
    border-radius: 5px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Đảm bảo padding của các nút trong alert không bị ảnh hưởng */
.alert .edit-button,
.alert .delete-button {
    padding: 5px 10px;  /* Giữ padding cố định cho nút */
    margin: 0;  /* Đảm bảo không có margin làm thay đổi layout */
}
        .user {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-height: 170px;
            overflow-y: auto;
        }

        .edit-button, .delete-button {
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .edit-button {
            background-color: #5bc0de;
            color: white;
        }

        .edit-button:hover {
            background-color: #31b0d5;
        }

        .delete-button {
            background-color: #d9534f;
            color: white;
        }

        .delete-button:hover {
            background-color: #c9302c;
        }

        /* Cải thiện giao diện bình luận */
        .comment {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 5px 0;
            max-height: 900px;
            overflow-y: auto;
            border-radius: 5px;
            position: relative;
        }

        /* Định dạng cho nút đăng xuất */
        .logout-button {
            background-color: #f0ad4e;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-button:hover {
            background-color: #ec971f;
        }

        /* Định dạng cho các nút quản lý */
        .management-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .management-button {
            padding: 10px 20px;
            background-color: #5bc0de;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .management-button:hover {
            background-color: #31b0d5;
        }

        /* Định dạng cho các ô tìm kiếm */
        .search-form {
            display: flex;
            align-items: center;
        }

        .search-input {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-left: 10px;
        }
   
        .redirect-button {
            background-color: #007bff; /* Màu nền */
            color: white; /* Màu chữ */
            border: none; /* Không viền */
            border-radius: 5px; /* Bo góc */
            padding: 8px 15px; /* Khoảng cách bên trong */
            font-size: 16px; /* Kích thước chữ */
            cursor: pointer; /* Hiệu ứng con trỏ */
            transition: background-color 0.3s; /* Hiệu ứng chuyển màu */
            transform: translate(510px, 164px);
        }

        .redirect-button:hover {
            background-color: #0056b3; /* Màu nền khi hover */
        }
    </style>
</head>
<body>
  <button class="redirect-button" onclick="window.location.href='ban.php'">Cấm User</button>

    <div class="container">
        <h1>Admin Panel</h1>
        <h2>Tổng số người dùng: <?php echo $total_users; ?></h2>

        <div class="management-buttons">
            <button class="management-button" onclick="changeSection('posts')">Quản lý bài viết</button>
            <button class="management-button" onclick="changeSection('users')">Quản lý người dùng</button>

            <form method="GET" class="search-form" action="admin.php">
                <input type="hidden" name="section" value="<?php echo isset($_GET['section']) ? $_GET['section'] : 'posts'; ?>">
                <input type="text" name="search" class="search-input" placeholder="<?php echo (isset($_GET['section']) && $_GET['section'] === 'users') ? 'Tìm người dùng' : 'Tìm bài viết'; ?>">
                <button type="submit" class="management-button">Tìm</button>
            </form>
        </div>

        <?php if (isset($_GET['section']) && $_GET['section'] === 'users'): ?>
            <h2>Quản lý người dùng</h2>
            <?php if ($search_results && $search_results->num_rows > 0): ?>
                <?php while ($user = $search_results->fetch_assoc()): ?>
                    <div class="user">
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                        <div>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="text" name="new_username" placeholder="Tên mới" required>
                                <button type="submit" name="edit_user" class="edit-button">Chỉnh sửa</button>
                            </form>
                            <a href="admin.php?delete_user=<?php echo $user['id']; ?>" class="delete-button">Xóa</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php elseif (isset($_GET['search']) && $_GET['section'] === 'users'): ?>
                <p style="color: gray; font-style: italic;">Không tìm thấy nội dung nào</p>
            <?php else: ?>
                <?php while ($user = $users->fetch_assoc()): ?>
                    <div class="user">
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                        <div>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="text" name="new_username" placeholder="Tên mới" required 
               pattern="^[a-zA-Z0-9]+$" 
               title="Vui lòng chỉ nhập ký tự chữ và số, không có khoảng trắng hoặc ký tự đặc biệt.">
                                <button type="submit" name="edit_user" class="edit-button">Chỉnh sửa</button>
                            </form>
                            <a href="admin.php?delete_user=<?php echo $user['id']; ?>" class="delete-button">Xóa</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        <?php else: ?>
            <h2>Quản lý bài viết</h2>
            <?php if ($search_results && $search_results->num_rows > 0): ?>
                <?php while ($post = $search_results->fetch_assoc()): ?>
                    <div class="post">
                        <h4><?php echo htmlspecialchars($post['content']); ?></h4>
                        <small>Đăng bởi: <?php echo htmlspecialchars($post['username']); ?> vào <?php echo $post['created_at']; ?></small>
                        <a href="admin.php?delete_post=<?php echo $post['id']; ?>" class="delete-button">Xóa bài viết</a>

                        <h5>Bình luận:</h5>
                        <?php if (isset($comments[$post['id']])): ?>
                            <?php foreach ($comments[$post['id']] as $comment): ?>
                                <div class="comment">
                                    <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                    <?php echo htmlspecialchars($comment['content']); ?>
                                    <a href="admin.php?delete_comment=<?php echo $comment['id']; ?>" class="delete-button" style="position: absolute; top: 10px; right: 10px;">Xóa</a>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Chưa có bình luận nào.</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php elseif (isset($_GET['search']) && $_GET['section'] === 'posts'): ?>
                <p style="color: gray; font-style: italic;">Không tìm thấy nội dung nào</p>
            <?php else: ?>
                <?php if ($posts->num_rows > 0): ?>
                    <?php while ($post = $posts->fetch_assoc()): ?>
                        <div class="post">
                            <h4><?php echo htmlspecialchars($post['content']); ?></h4>
                            <small>Đăng bởi: <?php echo htmlspecialchars($post['username']); ?> vào <?php echo $post['created_at']; ?></small>
                            <a href="admin.php?delete_post=<?php echo $post['id']; ?>" class="delete-button">Xóa bài viết</a>

                            <h5>Bình luận:</h5>
                            <?php if (isset($comments[$post['id']])): ?>
                                <?php foreach ($comments[$post['id']] as $comment): ?>
                                    <div class="comment">
                                        <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                        <a href="admin.php?delete_comment=<?php echo $comment['id']; ?>" class="delete-button" style="position: absolute; top: 10px; right: 10px;">Xóa</a>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>Chưa có bình luận nào.</p>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Chưa có bài viết nào.</p>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <a href="index.php?logout=true">
            <button class="logout-button">Đăng xuất</button>
        </a>
    </div>

    <script>
        function changeSection(section) {
            // Khi nhấn nút, trang sẽ tải lại với tham số "section"
            window.location.href = "admin.php?section=" + section;
        }
    </script>
</body>
</html>