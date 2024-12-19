<?php
session_start();
include('../config.php');
include('../app/admin/php.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="/app/admin/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<body>
<body>
<div class="container">
    <h1>Admin Panel</h1>
    <div class="welcome">
        <h4> Chào Admin,
            Cảm ơn bạn đã tham gia quản lý và phát triển website.Để tiếp tục sử dụng các chức năng quản trị và thực hiện các thay đổi cần thiết, vui lòng bấm vào nút 'Mở menu' bên dưới. Tại đây, bạn có thể truy cập vào các phần quan trọng như quản lý người dùng, chỉnh sửa bài viết và bình luận, và nhiều tính năng khác mà bạn đã xây dựng.Chúc bạn có những trải nghiệm tốt nhất khi quản lý website. 
        </h4> 
    </div>
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
                <span style="cursor: pointer;" onclick="changeSection('api')"><i class="fas fa-cogs"></i> API</span>
            </li>
            <li>
                <span style="cursor: pointer;" onclick="window.location.href='logs.php'"><i class="fas fa-book"></i> Logs</span>
            </li>
            <li>
                <span style="cursor: pointer;" onclick="window.location.href='ban.php'"><i class="fas fa-user-slash"></i> Cấm User</span>
            </li>
     
            <li>
                <span style="cursor: pointer;" onclick="window.location.href='index.php?logout=true'"><i class="fas fa-sign-out-alt"></i> Đăng xuất</span>
            </li>
        </ul>
        <form method="GET" class="search-form" action="src/admin.php">
            <input type="hidden" name="section" value="<?php echo isset($_GET['section']) ? $_GET['section'] : 'posts'; ?>">
            <input type="text" name="search" class="search-input" placeholder="<?php echo (isset($_GET['section']) && $_GET['section'] === 'users') ? 'Tìm người dùng' : 'Tìm bài viết'; ?>">
            <button type="submit" class="management-button">Tìm</button>
        </form>
    </nav>
    <div class="main-content">
        <button id="open-btn" class="open-btn">☰ Mở Menu</button>
    </div>

    <div id="content" class="hidden">
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
                                    <input type="text" name="new_username" placeholder="Tên mới" required pattern="^[a-zA-Z0-9]+$" title="Vui lòng chỉ nhập ký tự chữ và số, không có khoảng trắng hoặc ký tự đặc biệt.">
                                    <button type="submit" name="edit_user" class="edit-button">Chỉnh sửa</button>
                                </form>
                                <a href="src/admin.php?delete_user=<?php echo $user['id']; ?>" class="delete-button">Xóa</a>
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
                                <a href="src/admin.php?delete_user=<?php echo $user['id']; ?>" class="delete-button">Xóa</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>

        <?php elseif (isset($_GET['section']) && $_GET['section'] === 'posts'): ?>
            <h2>Quản lý bài viết</h2>
            <div class="user-list">
                <?php if ($search_results && $search_results->num_rows > 0): ?>
                    <?php while ($post = $search_results->fetch_assoc()): ?>
                        <div class="post">
                            <h4><?php echo htmlspecialchars($post['content']); ?></h4>
                            <small>Đăng bởi: <?php echo htmlspecialchars($post['username']); ?> vào <?php echo $post['created_at']; ?></small>
                            <a href="src/admin.php?delete_post=<?php echo $post['id']; ?>" class="delete-button">Xóa bài viết</a>

                            <h5>Bình luận:</h5>
                            <?php if (isset($comments[$post['id']])): ?>
                                <?php foreach ($comments[$post['id']] as $comment): ?>
                                    <div class="comment">
                                        <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                        <?php echo htmlspecialchars($comment['content']); ?>
                                        <a href="src/admin.php?delete_comment=<?php echo $comment['id']; ?>" class="delete-button" style="position: absolute; top: 10px; right: 10px;">Xóa</a>
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
                                <a href="src/admin.php?delete_post=<?php echo $post['id']; ?>" class="delete-button">Xóa bài viết</a>

                                <h5>Bình luận:</h5>
                                <?php if (isset($comments[$post['id']])): ?>
                                    <?php foreach ($comments[$post['id']] as $comment): ?>
                                        <div class="comment">
                                            <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
                                            <?php echo htmlspecialchars($comment['content']); ?>
                                            <a href="src/admin.php?delete_comment=<?php echo $comment['id']; ?>" class="delete-button" style="position: absolute; top: 10px; right: 10px;">Xóa</a>
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
            </div>

        <?php elseif (isset($_GET['section']) && $_GET['section'] === 'info'): ?>
            <div class="in4">   <h2>Thông tin</h2> </div>
            <div class="info-section">
                <p><strong>Tổng bài viết:</strong> <br> <?php echo $total_posts; ?></p>
                <p><strong>Tổng bình luận:</strong> <br> <?php echo $total_comments; ?></p>
                <p><strong>Tổng người dùng:</strong> <br> <?php echo $total_users; ?></p>
                <p><strong>Tổng người dùng / IP đang bị cấm :</strong> <br> <?php echo $total_bans; ?></p>
            </div>
            <?php elseif (isset($_GET['section']) && $_GET['section'] === 'api'): ?>
                <div class="in4">   <h2>Thông tin về API</h2> </div>
                <div class="info-section">
                <p><strong>Các bài viết:</strong> <br> <a href=/api/Post.php>/api/Post.php?api=[api key]</a></p>
                <p><strong>Các bình luận:</strong> <br> <a href=/api/Comment.php>/api/Comment.php?api=[api key]</a></p>
                <p><strong>Các người dùng:</strong> <br> <a href=/api/User.php>/api/User.php?api=[api key]</a> </p>
                <p><strong>Các người dùng / IP đang bị cấm :</strong> <br> <a href=/api/Bans.php>/api/Bans.php?api=[api key]</a></p>
                <h2>Bạn có thể tạo API Keys <a href="src/api.php">tại đây</a></h2>
                <h3>Hướng dẫn chi tiết : <strong><a href="/api_docs.html">Tại đây.</a></strong></h3>

</div>


<script>
    // Lấy domain hiện tại
    const domain = window.location.origin;

    // Cập nhật các URL API động
    document.getElementById('post-api').href = domain + '/api/Post.php';
    document.getElementById('bans-api').href = domain + '/api/Bans.php';
    document.getElementById('comments-api').href = domain + '/api/Comments.php';
    document.getElementById('user-api').href = domain + '/api/User.php';
</script>

        <?php endif; ?>
    </div>
</div>
<script src = "/app/admin/taskbar.js"></script>


</body>
</html>