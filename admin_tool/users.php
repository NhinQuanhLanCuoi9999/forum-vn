<?php
session_start();
include '../config.php';
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
  <?php if (!empty($message)) { echo $message; } ?>

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
            <div>
              <span class="fw-bold"><?php echo htmlspecialchars($user['username']); ?></span>
              <small class="text-muted">(Role: <?php echo htmlspecialchars($user['role']); ?>)</small>
            </div>
            <?php if ($session_role === 'owner'): ?>
              <!-- Owner: hiển thị dropdown với đầy đủ các option -->
              <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownActions_<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                  Hành động
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownActions_<?php echo $user['id']; ?>">
                  <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal_<?php echo $user['id']; ?>">Chỉnh sửa tên</a></li>
                  <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal_<?php echo $user['id']; ?>">Xóa tài khoản</a></li>
                  <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#permissionModal_<?php echo $user['id']; ?>">Phân quyền</a></li>
                </ul>
              </div>
              <?php elseif ($session_role === 'admin' && $user['role'] === 'member'): ?>
  <!-- Admin: sử dụng dropdown menu cho member -->
  <div class="dropdown">
    <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" id="dropdownActions_<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
      Hành động
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownActions_<?php echo $user['id']; ?>">
      <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editModal_<?php echo $user['id']; ?>">Chỉnh sửa tên</a></li>
      <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal_<?php echo $user['id']; ?>">Xóa tài khoản</a></li>
    </ul>
  </div>
<?php endif; ?>

          </div>
        </div>

        <!-- Modal chỉnh sửa tên -->
        <div class="modal fade" id="editModal_<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel_<?php echo $user['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-header">
                  <h5 class="modal-title" id="editModalLabel_<?php echo $user['id']; ?>">Chỉnh sửa tên tài khoản</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="mb-3">
                    <label for="new_username_<?php echo $user['id']; ?>" class="form-label">Tên mới:</label>
                    <input type="text" class="form-control" id="new_username_<?php echo $user['id']; ?>" name="new_username" placeholder="Tên mới" required pattern="^[a-zA-Z0-9]+$">
                  </div>
                  <p>Bạn có chắc chắn muốn đổi tên tài khoản <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                </div>
                <div class="modal-footer">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                  <button type="submit" name="edit_user" class="btn btn-warning">Đổi tên</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal xóa tài khoản -->
        <div class="modal fade" id="deleteModal_<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel_<?php echo $user['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="modal-header">
                  <h5 class="modal-title" id="deleteModalLabel_<?php echo $user['id']; ?>">Xác nhận xóa</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  Bạn có chắc chắn muốn xóa tài khoản <strong><?php echo htmlspecialchars($user['username']); ?></strong> không?
                </div>
                <div class="modal-footer">
                  <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                  <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                  <button type="submit" name="delete_user" class="btn btn-danger">Xóa</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal phân quyền (chỉ dành cho owner) -->
        <?php if ($session_role === 'owner'): ?>
        <div class="modal fade" id="permissionModal_<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="permissionModalLabel_<?php echo $user['id']; ?>" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <?php if ($user['role'] === 'owner'): ?>
                <div class="modal-header">
                  <h5 class="modal-title" id="permissionModalLabel_<?php echo $user['id']; ?>">Phân quyền</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="alert alert-warning">Không thể thay đổi quyền của owner.</div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
              <?php else: ?>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                  <div class="modal-header">
                    <h5 class="modal-title" id="permissionModalLabel_<?php echo $user['id']; ?>">Phân quyền</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <?php if ($user['role'] === 'admin'): ?>
                      <p>Bạn có chắc chắn muốn hủy quyền admin cho tài khoản <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                    <?php else: ?>
                      <p>Bạn có chắc chắn muốn thêm quyền admin cho tài khoản <strong><?php echo htmlspecialchars($user['username']); ?></strong>?</p>
                    <?php endif; ?>
                  </div>
                  <div class="modal-footer">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" name="change_permission" class="btn btn-info">
                      <?php echo ($user['role'] === 'admin') ? 'Hủy quyền' : 'Thêm quyền'; ?>
                    </button>
                  </div>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>

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
