<?php
include '../app/_USERS_LOGIC/search/LogicPHP/Handle/AJAX.php';
include('../config.php');
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
    <title>Tìm kiếm bài đăng</title>
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
    <link rel="stylesheet" href="../app/_USERS_LOGIC/search/style.css">
</head>
<body>
    <div class="container">
        <h1>Tìm kiếm bài đăng</h1>
        <div class="input-group mb-4">
            <input id="search-input" type="text" class="form-control" name="search" placeholder="Tìm kiếm bài đăng..." value="<?php echo htmlspecialchars($params['search']); ?>" />
        </div>
        <button id="advanced-search-btn" class="btn btn-primary">Tìm kiếm nâng cao</button>
        <div id="advanced-search-form">
            <div class="advanced-search-input">
                <label for="start_date" class="advanced-search-label">Từ ngày:</label>
                <input id="start_date" type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($params['start_date']); ?>" />
            </div>
            <div class="advanced-search-input">
                <label for="end_date" class="advanced-search-label">Đến ngày:</label>
                <input id="end_date" type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($params['end_date']); ?>" />
            </div>
        </div>
        <div id="results">
            <?php renderPosts($result); ?>
        </div>
        <div id="loading">
    <img src="../asset/gif/Loading.gif" alt="Loading..." style="width: 50px; height: auto;" />
</div>
    </div>
    <script src="../asset/js/Bootstrap.bundle.min.js"></script>
    <script src="../app/_USERS_LOGIC/search/AJAX.js"></script>
</body>
</html>
