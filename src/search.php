<?php
include '../app/_USERS_LOGIC/search/php.php';
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
    <title>Tìm kiếm bài đăng</title>

    <!-- Link đến Bootstrap CSS và Font Poppins -->
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" href="../asset/css/Bootstrap.min.css">
    <style>body{font-family:'Poppins',sans-serif;background-color:#f8f9fa;margin:0;padding:0}.container{max-width:900px;margin:30px auto;padding:30px;background-color:#fff;border-radius:10px;box-shadow:0 4px 6px rgba(0,0,0,.1)}h1{font-size:2.5rem;font-weight:600;color:#343a40;margin-bottom:30px;text-align:center}.form-control{border-radius:50px;padding:15px;font-size:16px;width:100%;box-sizing:border-box}.btn-primary,#advanced-search-btn{background-color:#007bff;border:none;padding:12px 30px;border-radius:30px;font-size:16px;color:white;transition:background-color .3s;cursor:pointer}.btn-primary:hover,#advanced-search-btn:hover{background-color:#0056b3}.post{border-bottom:1px solid #ddd;padding:20px;margin-bottom:20px;background-color:#f9f9f9;border-radius:10px}.pagination{display:flex;justify-content:center;flex-wrap:wrap}.pagination a{color:#007bff;text-decoration:none;padding:10px 15px;margin:0 5px;border-radius:50px;transition:background-color .3s}.pagination a:hover{background-color:#f1f1f1}.pagination .active{font-weight:bold;background-color:#007bff;color:white;padding:10px 15px;border-radius:50px}#advanced-search-form{display:block;overflow:hidden;max-height:0;transition:max-height .3s ease-out;padding:0 20px}.advanced-search-input{margin-bottom:15px}.advanced-search-label{font-weight:600;color:#495057}@media(max-width:768px){.container{max-width:100%;margin:15px;padding:20px}.h1{font-size:1.8rem}.form-control{border-radius:30px;padding:12px;font-size:14px}.btn-primary,#advanced-search-btn{padding:10px 20px;font-size:14px;border-radius:20px}.post{padding:15px;margin-bottom:15px}.pagination a{padding:8px 12px;font-size:14px}.advanced-search-input{margin-bottom:10px}} </style>

    
</head>
<body>

    <div class="container">
        <h1>Tìm kiếm bài đăng</h1>
        
        <!-- Tìm kiếm đơn giản -->
        <form method="GET" action="">
            <div class="input-group mb-4">
                <input type="text" class="form-control" name="search" placeholder="Tìm kiếm bài đăng..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" />
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </div>
        </form>

        <!-- Tìm kiếm nâng cao -->
        <button id="advanced-search-btn" class="btn btn-primary">Tìm kiếm nâng cao</button>
        <div id="advanced-search-form">
            <form method="GET" action="">
                <div class="advanced-search-input">
                    <label for="start_date" class="advanced-search-label">Từ ngày:</label>
                    <input type="date" class="form-control" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>" />
                </div>
                <div class="advanced-search-input">
                    <label for="end_date" class="advanced-search-label">Đến ngày:</label>
                    <input type="date" class="form-control" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>" />
                </div>
                <button type="submit" class="btn btn-primary">Tìm kiếm</button>
            </form>
        </div>

        <!-- Hiển thị kết quả tìm kiếm -->
        <div id="results">
    <?php
     while ($row = $result->fetch_assoc()) {
        echo "<div class='post'>";
        echo "<h3><a href='src/profile.php?username=" . $row['username'] . "'>" . $row['username'] . "</a></h3>";        
        echo "<p>" . $row['content'] . "</p>";
        echo "<small>" . "Ngày đăng: ". $row['created_at'] . "</small>";
        echo "<a href='src/view.php?id=" . $row['id'] . "' class='view-more'>Xem thêm</a>";
        echo "</div>";
    } // Đóng vòng lặp while

    ?>

        </div>
    </div>

    <!-- Script Bootstrap và hiệu ứng trượt -->
    <script src="../asset/js/Bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('advanced-search-btn').addEventListener('click', function() {
        var form = document.getElementById('advanced-search-form');
        
        // Kiểm tra trạng thái hiển thị của form
        if (form.style.maxHeight === '0px' || form.style.maxHeight === '') {
            // Mở form với hiệu ứng trượt
            form.style.display = 'block';
            setTimeout(function() {
                form.style.maxHeight = form.scrollHeight + "px";
            }, 10); 
        } else {form.style.maxHeight = '0px';setTimeout(function() {form.style.display = 'none'; }, 300);}
    });
</script>

</body>
</html>
