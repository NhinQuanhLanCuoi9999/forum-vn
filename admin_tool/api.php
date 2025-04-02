<?php
session_start();
include '../config.php'; 
include '../app/_ADMIN_TOOLS/api/php.php'; 

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
However, if you redistribute the source code, you must retain this license.
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản lý API</title>
  <!-- Bootstrap CSS -->
  <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/api/styles.css">
  <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/api/Pagination.css">
  <link rel="stylesheet" type="text/css" href="/asset/css/Poppins.css">
  <script src="/asset/js/Bootstrap.bundle.min.js"></script>
</head>
<body>
  <div class="container py-4">
    <h1 class="mb-4">Quản lý API Keys</h1>
    <!-- Nút quay về trang admin và nút tạo API Key -->
    <div class="d-flex align-items-center gap-2 mb-3">
      <a href="/admin_tool/admin.php" class="btn btn-primary">
        Về trang admin
      </a>
      <!-- Button trigger modal -->
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#apiKeyModal">
        Tạo API Key mới
      </button>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
      <div class="alert alert-info">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
      </div>
    <?php endif; ?>

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>API Key</th>
          <th>Trạng thái</th>
          <th>Ngày tạo</th>
          <th>Remaining Uses</th>
          <th>Hành động</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($apiKeys as $key): ?>
          <tr>
            <td><?= $key['id']; ?></td>
            <td><?= $key['api_key']; ?></td>
            <td><?= $key['is_active'] ? 'Kích hoạt' : 'Vô hiệu hóa'; ?></td>
            <td><?= $key['created_at']; ?></td>
            <td><?= $key['remaining_uses']; ?></td>
            <td>
              <a href="?toggle=<?= $key['id']; ?>" class="btn btn-warning btn-sm">
                <?= $key['is_active'] ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>
              </a>
              <a href="?delete=<?= $key['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">
                Xóa
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Phân trang -->
    <div class="pagination">
      <a href="?page=1" class="page-link <?= $page == 1 ? 'disabled' : ''; ?>"><<</a>
      <a href="?page=<?= $page - 1; ?>" class="page-link <?= $page == 1 ? 'disabled' : ''; ?>">‹</a>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i > $page - 5 && $i < $page + 5): ?>
          <a href="?page=<?= $i; ?>" class="page-link <?= $i == $page ? 'active' : ''; ?>">
            <?= $i; ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>
      <a href="?page=<?= $page + 1; ?>" class="page-link <?= $page == $totalPages ? 'disabled' : ''; ?>">›</a>
      <a href="?page=<?= $totalPages; ?>" class="page-link <?= $page == $totalPages ? 'disabled' : ''; ?>">>></a>
    </div>
  </div>

  <!-- Modal sử dụng Bootstrap -->
  <div class="modal fade" id="apiKeyModal" tabindex="-1" aria-labelledby="apiKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="apiKeyModalLabel">Chọn số lượng remaining_uses</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="remainingRange" class="form-label">Chọn số lượng:</label>
            <input type="range" class="form-range" id="remainingRange" min="500" max="2000" value="500" oninput="updateRangeValue(this.value)">
            <div>Số lượng: <span id="rangeValue">500</span></div>
          </div>
          <form method="POST" id="apiKeyForm">
            <input type="hidden" name="remaining_uses" id="remaining_uses" value="500">
            <button type="submit" name="confirm_generate_key" class="btn btn-success">
              Tạo API Key
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="/app/_ADMIN_TOOLS/api/Modal.js"></script>
  </script>
</body>
</html>
