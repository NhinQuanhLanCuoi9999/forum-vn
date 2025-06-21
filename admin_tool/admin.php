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
    <link rel="icon" href="/favicon.ico" type="image/png">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/admin/styles.css">
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
    <link rel="stylesheet" href="/asset/css/FontAwesome.min.css">
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/admin/Pagination.css">
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/admin/configsys.css">
    <script src="/asset/js/chart.js"></script>
    <script src = "/app/_ADMIN_TOOLS/admin/taskbar.js"></script>
</head>
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
    <li><span style="cursor: pointer;" onclick="window.location.href='backup.php'"><i class="fas fa-hdd"></i> Backup</span></li>
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
      <input type="text" id="title" name="title" value="<?= htmlspecialchars($system_config['title'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="name">Name:</label>
      <input type="text" id="name" name="name" value="<?= htmlspecialchars($system_config['name'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="info">Nội dung thông báo:</label>
      <input type="text" id="info" name="info" value="<?= htmlspecialchars($system_config['info'] ?? '') ?>">
    </div>
    <div class="form-row">
      <label for="turnstile_site_key">Turnstile Site Key:</label>
      <input type="password" id="turnstile_site_key" name="turnstile_site_key" value="<?= htmlspecialchars($system_config['turnstile_site_key'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="turnstile_api_key">Turnstile API Key:</label>
      <input type="password" id="turnstile_api_key" name="turnstile_api_key" value="<?= htmlspecialchars($system_config['turnstile_api_key'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <label for="ipinfo_api_key">IPInfo API Key:</label>
      <input type="password" id="ipinfo_api_key" name="ipinfo_api_key" value="<?= htmlspecialchars($system_config['ipinfo_api_key'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <button type="submit">Lưu thay đổi</button>
    </div>
  </div>
</form>




<strong><a href="advanced_config.php">Click vào đây </a> để cấu hình nâng cao.</strong> <br>



    <?php elseif (isset($_GET['section']) && $_GET['section'] === 'info'): ?>
<div class="container mt-4">
    <h3 class="fw-bold">Dashboard</h3>
    <div class="row g-3">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-3" style="background-color: #E0ECFF;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-primary">Users</h6>
                        <h2 class="fw-bold"><?php echo $total_users; ?></h2>
                        <p class="small text-secondary mb-0">Total Users</p>
                    </div>
                    <div class="p-3 bg-white rounded-circle shadow-sm">
                        <i class="fa-solid fa-users text-primary fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-3" style="background-color: #DDF5E1;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-success">Posts</h6>
                        <h2 class="fw-bold"><?php echo $total_posts; ?></h2>
                        <p class="small text-secondary mb-0">Total Posts</p>
                    </div>
                    <div class="p-3 bg-white rounded-circle shadow-sm">
                        <i class="fa-solid fa-file-alt text-success fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-3" style="background-color: #FFE6E6;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-danger">Comments</h6>
                        <h2 class="fw-bold"><?php echo $total_comments; ?></h2>
                        <p class="small text-secondary mb-0">Total Comments</p>
                    </div>
                    <div class="p-3 bg-white rounded-circle shadow-sm">
                        <i class="fa-solid fa-comments text-danger fs-2"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 rounded-3" style="background-color: #FFF3CD;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-warning">Banned Users</h6>
                        <h2 class="fw-bold"><?php echo $total_bans; ?></h2>
                        <p class="small text-secondary mb-0">Total Bans</p>
                    </div>
                    <div class="p-3 bg-white rounded-circle shadow-sm">
                        <i class="fa-solid fa-user-slash text-warning fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Biểu đồ thống kê người dùng theo tháng -->
<div class="container-fluid mt-4">
  <h4 class="fw-bold text-center">Số người dùng tạo tài khoản vào năm nay</h4>
  <!-- Container flex chính -->
  <div class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
    <!-- Khung chứa biểu đồ, cho phép co giãn theo chiều ngang -->
    <div style="flex: 1 1 100%; max-width: 1200px; padding: 0 15px;">
      <!-- Wrapper Chart.js: position relative và responsive height -->
      <div style="position: relative; height: 60vh; width: 100%;">
        <canvas id="userChart" style="width: 100%; height: 100%;"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const ctx = document.getElementById("userChart").getContext("2d");

  // Hủy biểu đồ cũ nếu có
  if (window.userChartInstance) {
    window.userChartInstance.destroy();
  }

  // Tạo biểu đồ mới
  window.userChartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      datasets: [{label: "Số người dùng mỗi tháng",data: <?php echo json_encode($user_data); ?>,borderColor: "#007bff",backgroundColor: "rgba(0, 123, 255, 0.2)",borderWidth: 4,pointRadius: 8,pointBackgroundColor: "#007bff",fill: true,tension: 0.3}]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {y: {beginAtZero: true,ticks: { font: { size: 18 } }},x: {ticks: { font: { size: 18 } }}},plugins: {legend: {labels: { font: { size: 18 } }},tooltip: {titleFont: { size: 18 },bodyFont: { size: 16 }}}}});});
</script>






<?php elseif (isset($_GET['section']) && $_GET['section'] === 'api'): ?>
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h2 class="mb-0"><i class="fa-solid fa-code"></i> Thông tin về API</h2>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a id="post-api" href="#" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-newspaper"></i> <strong>Các bài viết:</strong>  
                        <br><span class="text-muted">/api/Post.php?api=[api key]</span>
                    </a>
                    
                    <a id="comments-api" href="#" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-comments"></i> <strong>Các bình luận:</strong>  
                        <br><span class="text-muted">/api/Comment.php?api=[api key]</span>
                    </a>
                    
                    <a id="user-api" href="#" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-users"></i> <strong>Các người dùng:</strong>  
                        <br><span class="text-muted">/api/User.php?api=[api key]</span>
                    </a>
                    
                    <a id="bans-api" href="#" class="list-group-item list-group-item-action">
                        <i class="fa-solid fa-ban"></i> <strong>Các người dùng / IP đang bị cấm:</strong>  
                        <br><span class="text-muted">/api/Bans.php?api=[api key]</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <h2><i class="fa-solid fa-key"></i> Bạn có thể tạo API Keys  
                <a href="admin_tool/api.php" class="text-primary fw-bold">tại đây</a>
            </h2>
            <h3><i class="fa-solid fa-book"></i> Hướng dẫn chi tiết:  
                <strong><a href="/docs/api_docs.html" class="text-danger">Tại đây.</a></strong>
            </h3>
        </div>
    </div>
    <script src = "/app/_ADMIN_TOOLS/admin/url.js"></script>
<?php endif; ?>


    </div>
</div>


</body>
</html>