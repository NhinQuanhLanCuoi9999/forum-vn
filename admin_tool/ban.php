<?php
session_start();
include('../config.php');
include('../app/_ADMIN_TOOLS/ban/php.php');
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
    <link rel="icon" href="/favicon.png" type="image/png">
    <title>Quản lý lệnh cấm</title>
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <button class="btn btn-primary mb-3" onclick="window.location.href='admin_tool/admin.php'">Quay lại</button>
    
    <div class="card p-4">
        <h1 class="mb-3">Quản lý lệnh cấm</h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"> <?php echo $error_message; ?> </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"> <?php echo $success_message; ?> </div>
        <?php endif; ?>
        
        <form method="POST" onsubmit="return confirmBan();">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Tên người dùng (có thể để trống)">
            </div>
            <div class="mb-3">
                <input type="text" name="ip_address" class="form-control" placeholder="IP muốn cấm">
            </div>
            <div class="mb-3">
                <textarea name="reason" class="form-control" placeholder="Lý do cấm" required></textarea>
            </div>
            <div class="mb-3">
                <input type="datetime-local" name="ban_end" class="form-control" required>
                <small class="text-muted">Chọn ngày và giờ kết thúc lệnh cấm</small>
            </div>
            <button type="submit" name="ban" class="btn btn-danger">Cấm</button>
        </form>
    </div>
    
    <h2 class="mt-4">Danh sách người dùng bị cấm</h2>
    <div class="list-group">
        <?php if ($bans->num_rows === 0): ?>
            <p class="text-muted">Chưa có người dùng nào bị cấm.</p>
        <?php else: ?>
            <?php while ($ban = $bans->fetch_assoc()): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?php echo htmlspecialchars($ban['username'] ?? 'Không xác định'); ?></strong> - 
                        IP: <?php echo htmlspecialchars($ban['ip_address']); ?> - 
                        Lý do: <?php echo htmlspecialchars($ban['reason']); ?> - 
                        Đến: <?php echo htmlspecialchars($ban['ban_end']); ?>
                    </div>
                    <form method="post" action="ban.php" onsubmit="return confirmUnban();">
                        <input type="hidden" name="unban" value="<?php echo $ban['id']; ?>">
                        <button type="submit" class="btn btn-warning btn-sm">Hủy cấm</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
    
    <!-- Phân trang -->
    <nav class="mt-3" aria-label="Page navigation">
        <ul class="pagination">
            <?php if ($page > 1): ?>
                <li class="page-item"><a class="page-link" href="?page=1">&laquo;</a></li>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>">&lsaquo;</a></li>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"> <?php echo $i; ?> </a>
                </li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>">&rsaquo;</a></li>
                <li class="page-item"><a class="page-link" href="?page=<?php echo $totalPages; ?>">&raquo;</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <script src="/asset/js/Bootstrap.bundle.min.js"></script>
    <script src="../app/_ADMIN_TOOLS/ban/banOption.js"></script>
</body>
</html>