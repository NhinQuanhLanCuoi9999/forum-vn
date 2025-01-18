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
    <link rel="stylesheet" type="text/css" href="/app/admin/Pagination.css">
    <link rel="stylesheet" type="text/css" href="/app/admin/configsys.css">

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
        <span style="cursor: pointer;" onclick="changeSection('system_config')"><i class="fas fa-sliders-h"></i> Cấu hình hệ thống</span>
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
        
        <!-- Form tìm kiếm -->
        <form method="GET" action="admin.php">
            <input type="hidden" name="section" value="users">
            <input type="text" name="search" placeholder="Tìm kiếm người dùng" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button type="submit">Tìm kiếm</button>
            <style>
                form{display:flex;justify-content:center;align-items:center;margin:20px 0;padding:10px;background-color:#f1f1f1;border-radius:8px;box-shadow:0 4px 8px rgba(0,0,0,.1)}form input[type="text"]{padding:10px;width:250px;border:1px solid #007bff;border-radius:5px;margin-right:10px;font-size:16px;transition:border-color .3s ease}form input[type="text"]:focus{border-color:#0056b3;outline:none}form button{padding:10px 20px;background-color:#007bff;color:white;font-size:16px;border:none;border-radius:5px;cursor:pointer;transition:background-color .3s ease}form button:hover{background-color:#0056b3}form button:focus{outline:none}form input[type="text"]::placeholder{color:#888;font-style:italic}form input[type="text"]:empty::placeholder{color:#aaa;font-style:normal}
            </style>
        </form>

        <div class="user-list">
           

            <?php if ($users_result && $users_result->num_rows > 0): ?>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <div class="user">
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                        <div>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="text" name="new_username" placeholder="Tên mới" required pattern="^[a-zA-Z0-9]+$">
                                <button type="submit" name="edit_user" class="edit-button">Chỉnh sửa</button>
                            </form>
                            <a href="src/admin.php?delete_user=<?php echo $user['id']; ?>" class="delete-button">Xóa</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Chưa có người dùng nào.</p>
            <?php endif; ?>
        </div>
</div>


   <!-- Pagination links -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="admin.php?section=users&page=<?php echo $page - 1; ?>" class="prev">&lt;&lt;</a>
    <?php endif; ?>

    <!-- Display first page -->
    <?php if ($page > 3): ?>
        <a href="admin.php?section=users&page=1" class="page-link">1</a>
        <span>...</span>
    <?php endif; ?>

    <!-- Display page numbers -->
    <?php
        $start = max(1, $page - 2);
        $end = min($total_pages, $page + 2);

        for ($i = $start; $i <= $end; $i++): ?>
            <a href="admin.php?section=users&page=<?php echo $i; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
    <?php endfor; ?>

    <!-- Display last page -->
    <?php if ($page < $total_pages - 2): ?>
        <span>...</span>
        <a href="admin.php?section=users&page=<?php echo $total_pages; ?>" class="page-link"><?php echo $total_pages; ?></a>
    <?php endif; ?>

    <?php if ($page < $total_pages): ?>
        <a href="admin.php?section=users&page=<?php echo $page + 1; ?>" class="next">&gt;&gt;</a>
    <?php endif; ?>
</div>

<?php elseif (isset($_GET['section']) && $_GET['section'] === 'system_config'): ?>
    <h2>Cấu hình hệ thống</h2>


    <form method="POST" action="admin.php?section=system_config">
        <div class="form-table">
            <div class="form-row">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($system_config['title']); ?>" required>
            </div>
            <div class="form-row">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($system_config['name']); ?>" required>
            </div>
            <div class="form-row">
                <label for="hcaptcha_api_key">hCaptcha API Key:</label>
                <input type="text" id="hcaptcha_api_key" name="hcaptcha_api_key" value="<?php echo htmlspecialchars($system_config['hcaptcha_api_key']); ?>" required>
            </div>
            <div class="form-row">
                <label for="ipinfo_api_key">IPInfo API Key:</label>
                <input type="text" id="ipinfo_api_key" name="ipinfo_api_key" value="<?php echo htmlspecialchars($system_config['ipinfo_api_key']); ?>" required>
            </div>
            <div class="form-row">
                <button type="submit">Lưu thay đổi</button>
            </div>
        </div>
    </form>

<?php elseif (isset($_GET['section']) && $_GET['section'] === 'posts'): ?>
    <h2>Quản lý bài viết</h2>
    <div class="user-list">
        <?php if ($posts_result && $posts_result->num_rows > 0): ?>
            <?php while ($post = $posts_result->fetch_assoc()): ?>
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

       <!-- Pagination links -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="admin.php?section=posts&page=1" class="prev">&lt;&lt; Trang đầu</a>
    <?php endif; ?>

    <!-- Hiển thị các số trang với ... -->
    <?php if ($total_pages > 5): ?>
        <?php if ($page > 3): ?>
            <a href="admin.php?section=posts&page=1">1</a>
            <span>...</span>
        <?php endif; ?>

        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
            <a href="admin.php?section=posts&page=<?php echo $i; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages - 2): ?>
            <span>...</span>
            <a href="admin.php?section=posts&page=<?php echo $total_pages; ?>"><?php echo $total_pages; ?></a>
        <?php endif; ?>
    <?php else: ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="admin.php?section=posts&page=<?php echo $i; ?>" class="page-link <?php echo ($i == $page) ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
    <?php endif; ?>

    <?php if ($page < $total_pages): ?>
        <a href="admin.php?section=posts&page=<?php echo $total_pages; ?>" class="next">Trang cuối &gt;&gt;</a>
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
                <h3>Hướng dẫn chi tiết : <strong><a href="/docs/api_docs.html">Tại đây.</a></strong></h3>

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