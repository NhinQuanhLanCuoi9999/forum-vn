<?php
// 404.php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <title>Trang Không Tìm Thấy</title>
    <style>
        body {
              font-family: 'Poppins', sans-serif;

            background: linear-gradient(to right, #74ebd5, #9face6);
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            box-shadow: inset 0 0 100px rgba(255, 255, 255, 0.5);
        }

        h1 {
            font-size: 4em;
            color: #e74c3c; /* Màu đỏ cho tiêu đề */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            margin-bottom: 10px;
        }

        p {
            font-size: 1.5em;
            margin-bottom: 30px;
            color: #555; /* Màu xám cho nội dung */
            transition: color 0.3s ease;
        }

        p:hover {
            color: #e74c3c; /* Đổi màu khi hover */
        }

        a {
            text-decoration: none;
            color: #fff; /* Màu trắng cho liên kết */
            font-size: 1.2em;
            background-color: #3498db; /* Màu xanh cho nút */
            border: 2px solid #3498db;
            padding: 15px 30px;
            border-radius: 25px;
            transition: background-color 0.3s, color 0.3s, transform 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        a:hover {
            background-color: #2980b9; /* Màu xanh đậm hơn khi hover */
            color: #fff;
            transform: translateY(-2px);
        }

        /* Hiệu ứng cho phần tử */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        body {
            animation: fadeIn 1s ease-in;
        }
    </style>
</head>
<body>
    <h1>404 - Trang Không Tìm Thấy</h1>
    <p>Xin lỗi, trang bạn tìm không tồn tại.</p>
    <a href="/">Trở về trang chủ</a>
</body>
</html>