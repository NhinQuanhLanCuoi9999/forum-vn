<?php
session_start();
include('../config.php');
include('../app/_ADMIN_TOOLS/admin/logicPHP/Auth.php');
include '../app/_ADMIN_TOOLS/usermgr/php.php';
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
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quản lý người dùng</title>
  <!-- Thêm Bootstrap CSS -->
  <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
</head>
<body>
 <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">Quản lý người dùng</a>

    <!-- Nút "Về trang Admin" -->
    <a href="/admin_tool/admin.php" class="btn btn-outline-light ms-auto">Về trang Admin</a>
  </div>
</nav>

  <div class="container my-4">
    <h2 class="mb-4">Quản lý người dùng</h2>
    
    <!-- In thông báo nếu có -->
    <?php if (!empty($message)) {
      echo $message;
    } ?>

    <!-- Form tìm kiếm -->
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mb-4">
      <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm người dùng" value="<?php echo htmlspecialchars($search_term); ?>">
        <button type="submit" name="search_submit" class="btn btn-primary">Tìm kiếm</button>
      </div>
    </form>

    <!-- Danh sách người dùng -->
    <div class="user-list">
      <?php if ($users_result && $users_result->num_rows > 0): ?>
        <?php while ($user = $users_result->fetch_assoc()): ?>
          <div class="card mb-3">
            <div class="card-body d-flex justify-content-between align-items-center">
              <span><?php echo htmlspecialchars($user['username']); ?></span>
              <div>
                <!-- Form chỉnh sửa -->
                <form method="POST" class="d-inline-block">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                  <input type="text" name="new_username" class="form-control d-inline-block" style="width: auto;" placeholder="Tên mới" required pattern="^[a-zA-Z0-9]+$">
                  <button type="submit" name="edit_user" class="btn btn-warning btn-sm ms-2">Chỉnh sửa</button>
                </form>
                <!-- Form xóa -->
                <form method="POST" class="d-inline-block ms-2" onsubmit="return confirm('Bạn có chắc chắn muốn xóa tài khoản này không?');">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                  <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Xóa</button>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p class="text-center">Chưa có người dùng nào.</p>
      <?php endif; ?>
    </div>

    <!-- Phân trang qua POST -->
<div class="d-flex justify-content-center">
  <?php if ($page > 1): ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-inline-block me-2">
      <?php if (!empty($search_term)): ?>
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="hidden" name="search_submit" value="1">
      <?php endif; ?>
      <input type="hidden" name="page" value="1">
      <button type="submit" class="btn btn-secondary">&lt;&lt;&lt;</button>
    </form>
  <?php endif; ?>

  <?php
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for ($i = $start; $i <= $end; $i++):
  ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-inline-block me-2">
      <?php if (!empty($search_term)): ?>
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="hidden" name="search_submit" value="1">
      <?php endif; ?>
      <input type="hidden" name="page" value="<?php echo $i; ?>">
      <button type="submit" class="btn <?php echo ($i == $page) ? 'btn-primary' : 'btn-outline-secondary'; ?>"><?php echo $i; ?></button>
    </form>
  <?php endfor; ?>

  <?php if ($page < $total_pages): ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-inline-block me-2">
      <?php if (!empty($search_term)): ?>
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search_term); ?>">
        <input type="hidden" name="search_submit" value="1">
      <?php endif; ?>
      <input type="hidden" name="page" value="<?php echo $total_pages; ?>">
      <button type="submit" class="btn btn-secondary">&gt;&gt;&gt;</button>
    </form>
  <?php endif; ?>
</div>
      </div>
  <!-- Bootstrap Bundle JS (bao gồm Popper) -->
  <script src="/asset/js/Bootstrap.bundle.min.js"></script>
</body>
</html>
