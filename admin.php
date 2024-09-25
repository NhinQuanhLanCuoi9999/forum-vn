<?php
session_start();
include('config.php');

// Kiểm tra nếu người dùng đã đăng nhập thông qua cookie
if (isset($_COOKIE['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}

// Chỉ cho phép người dùng "admin" truy cập vào trang này
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: index.html");
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

// Xóa người dùng
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    // Cảnh báo trước khi xóa
    echo "<script>
            if(confirm('Bạn có chắc chắn muốn xóa tài khoản này không?')) {
                window.location.href = 'admin.php?confirm_delete_user=$user_id';
            }
          </script>";
}

// Xác nhận xóa người dùng
if (isset($_GET['confirm_delete_user'])) {
    $user_id = $_GET['confirm_delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Chỉnh sửa tên người dùng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $user_id = $_POST['user_id'];
    $new_username = $_POST['new_username'];

    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $new_username, $user_id);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    // Cảnh báo trước khi xóa bình luận
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
    header("Location: admin.php");
    exit();
}

// Xóa bài viết
if (isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    // Cảnh báo trước khi xóa bài viết
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
    header("Location: admin.php");
    exit();
}

// Lấy danh sách người dùng
$users = $conn->query("SELECT * FROM users");

// Lấy danh sách bài viết và bình luận
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
$comments = $conn->query("SELECT * FROM comments");

$total_users = $users->num_rows; // Tổng số người dùng

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
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

        .user, .post, .comment {
            background: #fff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .user {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>
        <h2>Tổng số người dùng: <?php echo $total_users; ?></h2>

        <h2>Quản lý người dùng</h2>
        <?php while ($user = $users->fetch_assoc()): ?>
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

        <h2>Quản lý bài viết và bình luận</h2>
        <h3>Bài viết:</h3>
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="post">
                    <h4><?php echo htmlspecialchars($post['content']); ?></h4>
                    <small>Đăng bởi: <?php echo htmlspecialchars($post['username']); ?> vào <?php echo $post['created_at']; ?></small>
                    <a href="admin.php?delete_post=<?php echo $post['id']; ?>" class="delete-button">Xóa bài viết</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Chưa có bài viết nào.</p>
        <?php endif; ?>

        <h3>Bình luận:</h3>
        <?php if ($comments->num_rows > 0): ?>
            <?php while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                    <?php echo htmlspecialchars($comment['content']); ?>
                    <a href="admin.php?delete_comment=<?php echo $comment['id']; ?>" class="delete-button" style="position: absolute; top: 10px; right: 10px;">Xóa</a>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Chưa có bình luận nào.</p>
        <?php endif; ?>

        <a href="index.php?logout=true">
            <button class="logout-button">Đăng xuất</button>
        </a>
    </div>
</body>
</html>
