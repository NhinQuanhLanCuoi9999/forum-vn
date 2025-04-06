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
However, if you redistribute the source code, you must retain this license.
*/

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="/favicon.ico" type="image/png">
    <title>Quản lý lệnh cấm</title>
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
    <style>
        /* Style cho khung tìm kiếm bên phải */
        .search-panel { border: 1px solid #ddd; border-radius: 4px; padding: 15px; }
    </style>
</head>
<body class="container py-4">
    <button class="btn btn-primary mb-3" onclick="window.location.href='admin_tool/admin.php'">Quay lại</button>
    
    <div class="row">
        <!-- Nội dung chính bên trái -->
        <div class="col-md-8">
            <div class="card p-4 mb-4">
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
            <div class="list-group" id="banList">
                <?php renderBanList($bans); ?>
            </div>
            
            <!-- Phân trang cho trang chính -->
            <?php renderPagination($currentPage, $totalPages); ?>
        </div>
        
        <!-- Khung tìm kiếm bên phải -->
        <div class="col-md-4">
            <div class="search-panel">
                <h4>Tìm kiếm</h4>
                <input type="text" id="searchInput" class="form-control mb-3" placeholder="Nhập từ khóa tìm kiếm...">
                <div id="searchResults">
                    <!-- Kết quả tìm kiếm sẽ hiển thị ở đây -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="/asset/js/Bootstrap.bundle.min.js"></script>
    <script src="../app/_ADMIN_TOOLS/ban/banOption.js"></script>
    <script src="../app/_ADMIN_TOOLS//ban/SearchAJAX.js"></script>
</body>
</html>