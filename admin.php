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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
body {
    font-family: 'Poppins', sans-serif; /* Font chữ */
    background-color: #f4f4f4; /* Màu nền của body */
    color: #333; /* Màu chữ chính */
    margin: 0; /* Đảm bảo không có margin mặc định */
    overflow: hidden; /* Ẩn thanh cuộn của trang khi sidebar mở */
}

.sidebar {
    height: 100vh; /* Chiều cao đầy đủ của màn hình */
    width: 250px; /* Độ rộng của sidebar */
    background-color: #2c3e50; /* Màu nền */
    color: white; /* Màu chữ */
    position: fixed; /* Đặt sidebar cố định */
    left: -250px; /* Bắt đầu ngoài màn hình */
    transition: left 0.3s ease; /* Hiệu ứng trượt */
    padding-top: 20px; /* Padding trên cùng */
    overflow-y: auto; /* Cuộn nếu cần */
    z-index: 1000; /* Đảm bảo sidebar nằm trên cùng */
}

.sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background-color: #34495e; /* Màu nền của header */
    overflow: auto; /* Thêm overflow auto */
}

.sidebar-menu {
    list-style: none; /* Bỏ đánh dấu cho danh sách */
    padding: 0; /* Không có padding */
    overflow: auto; /* Thêm overflow auto */
}

.sidebar-menu li {
    padding: 10px 20px; /* Padding cho từng mục */
    transition: background-color 0.3s; /* Hiệu ứng màu nền khi hover */
}

.sidebar-menu li:hover {
    background-color: #2980b9; /* Màu nền khi hover */
}

.sidebar-menu li a {
    color: white; /* Màu chữ cho liên kết */
    text-decoration: none; /* Không gạch chân */
}

.main-content {
    margin-left: 0; /* Bắt đầu không có margin */
    padding: 20px; /* Padding cho nội dung chính */
    width: calc(100% - 250px); /* Chiếm toàn bộ không gian còn lại */
    transition: margin-left 0.3s ease; /* Hiệu ứng cho nội dung chính */
}

.open-btn {
    background-color: #007bff; /* Màu nền cho nút mở */
    color: white; /* Màu chữ */
    border: none; /* Không viền */
    padding: 10px 15px; /* Padding cho nút */
    cursor: pointer; /* Con trỏ khi hover */
    border-radius: 5px; /* Bo tròn góc */
    margin-bottom: 20px; /* Khoảng cách dưới cùng */
}

.open-btn:hover {
    background-color: #0056b3; /* Màu nền khi hover */
}

.show-sidebar {
    left: 0; /* Hiển thị sidebar khi có class này */
}

.show-content {
    margin-left: 250px; /* Đẩy nội dung sang phải khi sidebar mở */
}

.close-btn {
    cursor: pointer; /* Con trỏ khi hover */
    font-size: 24px; /* Kích thước chữ */
    color: white; /* Màu chữ */
}

.container {
    display: flex;
    flex-direction: column; /* Chuyển thành cột để tiêu đề và nội dung nằm thẳng hàng */
}

h1, h2 {
    white-space: nowrap; /* Ngăn chặn dòng văn bản bị bẻ */
    overflow: hidden;    /* Ẩn văn bản vượt quá chiều rộng */
    text-overflow: ellipsis; /* Thêm dấu ba chấm (...) nếu văn bản bị cắt */
    margin-bottom: 20px; /* Thêm khoảng cách dưới các tiêu đề */
}

.alert {
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    border: 1px solid transparent;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.user, .post, .comment {
    background: #fff;
    padding: 15px;
    margin: 10px 0;
    border-radius: 5px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    max-height: 170px;
    overflow-y: auto; /* Đã có rồi */
}
.user-list {
    max-height: 900px; /* Chiều cao tối đa cho khu vực cuộn */
    overflow-y: auto; /* Hiển thị thanh cuộn dọc nếu nội dung vượt quá chiều cao */
    border: 1px solid #ccc; /* Đường viền cho khu vực cuộn */
    padding: 30px; /* Khoảng cách bên trong */
    margin-bottom: 20px; /* Khoảng cách dưới cùng */
    background: #fff; /* Màu nền */
    border-radius: 5px; /* Làm tròn góc */
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); /* Bóng đổ */
}

/* Tùy chỉnh thanh cuộn */
.user-list::-webkit-scrollbar {
    width: 16px; /* Chiều rộng thanh cuộn (tăng lên) */
}

.user-list::-webkit-scrollbar-track {
    background: #f1f1f1; /* Màu nền cho track (vùng trượt) */
    border-radius: 5px; /* Làm tròn góc cho track */
}

.user-list::-webkit-scrollbar-thumb {
    background: #888; /* Màu của thanh cuộn */
    border-radius: 5px; /* Làm tròn góc cho thanh cuộn */
}

.user-list::-webkit-scrollbar-thumb:hover {
    background: #555; /* Màu của thanh cuộn khi hover */
}
.user {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px; /* Khoảng cách bên trong cho mỗi user */
    border-bottom: 1px solid #eee; /* Đường viền dưới mỗi user */
    overflow: auto; /* Thêm overflow auto */
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

.comment {
    border: 1px solid #ddd;
    padding: 10px;
    margin: 5px 0;
    max-height: 900px;
    overflow-y: auto; /* Đã có rồi */
    border-radius: 5px;
    position: relative;
}

.management-buttons {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    overflow: auto; /* Thêm overflow auto */
}

.management-button {
    padding: 5px 10px;
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

.search-form {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    overflow: auto; /* Thêm overflow auto */
}

.search-input {
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 5px;
    margin-left: 5px;
    flex: 1;
}
.total {
    transform: translate(18cm, -150px);
}
.info-section {
    display: flex;
    flex-direction: column;
    width: 100%; /* Chiếm toàn bộ chiều rộng */
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
}

.info-section p {
    width: 100%; /* Chiếm toàn bộ chiều rộng của phần tử cha */
}

.info-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px; /* Khoảng cách giữa các phần tử */
    padding: 20px;
    background-color: #fff; /* Màu nền trắng */
    border-radius: 10px; /* Làm tròn góc */
    box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1); /* Bóng đổ */
    width: 100%; /* Đảm bảo phần tử cha có chiều dài toàn màn hình */
}

.info-section p {
    font-size: 24px;
    font-weight: bold;
    color: #333;
    text-align: center;
    width: 90%; /* Chiều rộng mỗi phần tử hơi to hơn */
    max-width: 100%; /* Đảm bảo không vượt quá màn hình */
    margin: 0 auto; /* Canh giữa phần tử trong màn hình */
    padding: 10px; /* Thêm khoảng cách trong phần tử */
}

/* Tùy chỉnh màu nền cho từng phần tử (nếu muốn) */
.info-section p:nth-child(1) {
    background-color: #2196F3; /* Màu xanh dương */
}
.info-section p:nth-child(2) {
    background-color: #FFEB3B; /* Màu vàng */
}
.info-section p:nth-child(3) {
    background-color: #4CAF50; /* Màu xanh lá */
}
.info-section p:nth-child(4) {
    background-color: #F44336; /* Màu đỏ */
}
.info-section p:nth-child(5) {
    background-color: Azure ; /* Màu xanh nhạt */
}
/* Làm tròn góc cho từng phần tử */
.info-section p {
    border-radius: 10px;
}
.in4 {
    text-align: center;
}
.welcome {
    font-size: 10px;
}
</style>

</head>
<body>
<div class="container">
    <h1>Admin Panel</h1>
    <div class ="welcome">
   <h4> Chào Admin,

Cảm ơn bạn đã tham gia quản lý và phát triển website.Để tiếp tục sử dụng các chức năng quản trị và thực hiện các thay đổi cần thiết, vui lòng bấm vào nút 'Mở menu' bên dưới. Tại đây, bạn có thể truy cập vào các phần quan trọng như quản lý người dùng, chỉnh sửa bài viết và bình luận, và nhiều tính năng khác mà bạn đã xây dựng.Chúc bạn có những trải nghiệm tốt nhất khi quản lý website. </h4> </div>
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Dashboard</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fas fa-home"></i> Trang chính</a></li>
            <li>
                <span style="cursor: pointer;" onclick="changeSection('info')"><i class="fas fa-info-circle"></i> Thông tin</span>
            </li>
            <li>
                <span style="cursor: pointer;" onclick="changeSection('posts')"><i class="fas fa-file-alt"></i> Quản lý bài viết</span>
            </li>
            <li>
                <span style="cursor: pointer;" onclick="changeSection('users')"><i class="fas fa-users"></i> Quản lý người dùng</span>
            </li>
            <li>
                <span style="cursor: pointer;" onclick="window.location.href='ban.php'"><i class="fas fa-user-slash"></i> Cấm User</span>
            </li>
            <li>
                <span style="cursor: pointer;" onclick="window.location.href='index.php?logout=true'"><i class="fas fa-sign-out-alt"></i> Đăng xuất</span>
            </li>
        </ul>
        <form method="GET" class="search-form" action="admin.php">
            <input type="hidden" name="section" value="<?php echo isset($_GET['section']) ? $_GET['section'] : 'posts'; ?>">
            <input type="text" name="search" class="search-input" placeholder="<?php echo (isset($_GET['section']) && $_GET['section'] === 'users') ? 'Tìm người dùng' : 'Tìm bài viết'; ?>">
            <button type="submit" class="management-button">Tìm</button>
        </form>
    </nav>
    <div class="main-content">
        <button id="open-btn" class="open-btn">☰ Mở Menu</button>
    </div>

    <div id="content" class="hidden">
  <style>
    .hidden {
        display: none;
    }
</style>

              <?php if (isset($_GET['section']) && $_GET['section'] === 'users'): ?>
            <h2>Quản lý người dùng</h2>
      <div class="user-list">
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
                                <input type="text" name="new_username" placeholder="Tên mới" required pattern="^[a-zA-Z0-9]+$" title="Vui lòng chỉ nhập ký tự chữ và số, không có khoảng trắng hoặc ký tự đặc biệt.">
                                <button type="submit" name="edit_user" class="edit-button">Chỉnh sửa</button>
                            </form>
                            <a href="admin.php?delete_user=<?php echo $user['id']; ?>" class="delete-button">Xóa</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        <?php elseif (isset($_GET['section']) && $_GET['section'] === 'posts'): ?>
            <h2>Quản lý bài viết</h2>
        <div class="user-list">
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
        <?php elseif (isset($_GET['section']) && $_GET['section'] === 'info'): ?>
        <div class ="in4">    <h2>Thông tin</h2> </div>
            <div class="info-section">
                <p><strong>Tổng bài viết:</strong> <br> <?php echo $total_posts; ?></p>
                <p><strong>Tổng bình luận:</strong> <br> <?php echo $total_comments; ?></p>
                <p><strong>Tổng người dùng:</strong> <br> <?php echo $total_users; ?></p>
                     <p><strong>Tổng người dùng / IP đang bị cấm :</strong> <br> <?php echo $total_bans; ?></p>

            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const openBtn = document.getElementById("open-btn");
        const sidebar = document.getElementById("sidebar");
        const mainContent = document.querySelector(".main-content");
        const contentDiv = document.getElementById("content");
        const welcomeDiv = document.querySelector(".welcome"); // Thêm dòng này

        // Sự kiện click để mở/đóng sidebar
        openBtn.addEventListener("click", () => {
            if (sidebar.classList.contains("show-sidebar")) {
                sidebar.classList.remove("show-sidebar");
                mainContent.classList.remove("show-content");
                openBtn.textContent = "☰ Mở Menu"; // Đổi chữ về "Mở Menu"
            } else {
                sidebar.classList.add("show-sidebar");
                mainContent.classList.add("show-content");
                openBtn.textContent = "✖ Đóng Menu"; // Đổi chữ thành "Đóng Menu"
            }
        });

        // Hiển thị nội dung nếu có section
        const urlParams = new URLSearchParams(window.location.search);
        const section = urlParams.get('section');
        if (section) {
            contentDiv.classList.remove('hidden');
            welcomeDiv.style.display = 'none'; // Ẩn div "welcome"
        }
    });

    function changeSection(section) {
        window.location.href = "admin.php?section=" + section;
    }
</script>

</body>
</html>