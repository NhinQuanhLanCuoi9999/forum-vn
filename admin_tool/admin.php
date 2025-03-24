<?php
session_start();
include('../config.php');
include('../app/_ADMIN_TOOLS/admin/php.php');
/*
##############################################################
#                                                            #
# This is the LICENSE file of Forum VN                       #
# Copyright belongs to Forum VN, Original Author:            #
# NhinQuanhLanCuoi9999                                       #
#                                                            #
##############################################################

Copyright © 2025 Forum VN  
Original Author: NhinQuanhLanCuoi9999  
License: GNU General Public License v3.0  

You are free to use, modify, and distribute this software under the terms of the GPL v3.  
However, if you redistribute the source code, you must retain this license.  */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.png" type="image/png">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/admin/styles.css">
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" href="/asset/css/FontAwesome.min.css">
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/admin/Pagination.css">
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/admin/configsys.css">

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
    <li><a href="/index.php"><i class="fas fa-home"></i> Trang chính</a></li>
    <li><span style="cursor: pointer;" onclick="changeSection('info')"><i class="fas fa-info-circle"></i> Thông tin</span></li>
    <li><span style="cursor: pointer;" onclick="changeSection('system_config')"><i class="fas fa-sliders-h"></i> Cấu hình hệ thống</span></li>
    <li><span style="cursor: pointer;" onclick="window.location.href='post.php'"><i class="fas fa-file-alt"></i> Quản lý bài viết</span></li>
    <li><span style="cursor: pointer;" onclick="window.location.href='users.php'"><i class="fas fa-users"></i> Quản lý người dùng</span></li>
    <li><span style="cursor: pointer;" onclick="changeSection('api')"><i class="fas fa-cogs"></i> API</span></li>
    <li><span style="cursor: pointer;" onclick="window.location.href='logs.php'"><i class="fas fa-book"></i> Logs</span></li>
    <li><span style="cursor: pointer;" onclick="window.location.href='backup.php'"><i class="fas fa-hdd"></i> Backup [BETA]</span></li>
    <li><span style="cursor: pointer;" onclick="window.location.href='ban.php'"><i class="fas fa-user-slash"></i> Cấm User</span></li>
    <li><span style="cursor: pointer;" onclick="window.location.href='/index.php?logout=true'"><i class="fas fa-sign-out-alt"></i> Đăng xuất</span></li>
    </ul>

      
    </nav>
    <div class="main-content">
        <button id="open-btn" class="open-btn">☰ Mở Menu</button>
    </div>
    <div id="content" class="hidden">
     
<?php if (isset($_GET['section']) && $_GET['section'] === 'system_config'): ?>
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
            <label for="info">Nội dung thông báo:</label>
            <input type="text" id="info" name="info" value="<?php echo htmlspecialchars((string)($system_config['info'] ?? 'Không có nội dung'), ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="form-row">
            <label for="turnstile_site_key">Turnstile Site Key:</label>
            <input type="text" id="turnstile_site_key" name="turnstile_site_key" value="<?php echo htmlspecialchars($system_config['turnstile_site_key'] ?? ''); ?>" required>
        </div>
        <div class="form-row">
            <label for="turnstile_api_key">Turnstile API Key:</label>
            <input type="text" id="turnstile_api_key" name="turnstile_api_key" value="<?php echo htmlspecialchars($system_config['turnstile_api_key'] ?? ''); ?>" required>
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


    <strong><a href="smtp_config.php">Click vào đây </a> để cấu hình SMTP.</strong>



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
                <h2>Bạn có thể tạo API Keys <a href="admin_tool/api.php">tại đây</a></h2>
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
<script src = "/app/_ADMIN_TOOLS/admin/taskbar.js"></script>


</body>
</html>