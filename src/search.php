<?php
include '../app/search/php.php';
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm bài đăng</title>

    <!-- Link đến Bootstrap CSS và Font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-control {
            border-radius: 50px;
            padding: 15px;
            font-size: 16px;
        }

        .btn-primary, #advanced-search-btn {
            background-color: #007bff;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            font-size: 16px;
            color: white;
            transition: background-color 0.3s;
        }

        .btn-primary:hover, #advanced-search-btn:hover {
            background-color: #0056b3;
        }

        .post {
            border-bottom: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
        }

        .pagination {
            justify-content: center;
        }

        .pagination a {
            color: #007bff;
            text-decoration: none;
            padding: 10px 15px;
            margin: 0 5px;
            border-radius: 50px;
            transition: background-color 0.3s;
        }

        .pagination a:hover {
            background-color: #f1f1f1;
        }

        .pagination .active {
            font-weight: bold;
            background-color: #007bff;
            color: white;
        }

        #advanced-search-form {
    display: block;
    overflow: hidden;
    max-height: 0; /* Ẩn form ban đầu */
    transition: max-height 0.3s ease-out; /* Hiệu ứng mượt mà */
    padding: 0 20px; /* Thêm padding để form không bị dính vào cạnh */
}


        .advanced-search-input {
            margin-bottom: 15px;
        }

        .advanced-search-label {
            font-weight: 600;
            color: #495057;
        }
    </style>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('advanced-search-btn').addEventListener('click', function() {
        var form = document.getElementById('advanced-search-form');
        
        // Kiểm tra trạng thái hiển thị của form
        if (form.style.maxHeight === '0px' || form.style.maxHeight === '') {
            // Mở form với hiệu ứng trượt
            form.style.display = 'block';
            setTimeout(function() {
                form.style.maxHeight = form.scrollHeight + "px"; // Cập nhật chiều cao tự động
            }, 10); // Đảm bảo hiệu ứng xảy ra sau khi form được hiển thị
        } else {
            // Đóng form với hiệu ứng trượt
            form.style.maxHeight = '0px';
            setTimeout(function() {
                form.style.display = 'none'; // Ẩn hoàn toàn sau khi đóng
            }, 300); // Đợi cho hiệu ứng hoàn thành
        }
    });
</script>

</body>
</html>
