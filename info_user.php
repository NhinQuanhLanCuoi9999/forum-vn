<?php
session_start(); // Bắt đầu phiên
include 'config.php'; // Kết nối tới cơ sở dữ liệu

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['username'])) {
    // Nếu người dùng chưa đăng nhập, chuyển hướng về index.php
    header("Location: index.php");
    exit(); // Đảm bảo dừng thực thi mã sau khi chuyển hướng
}

$username = $_SESSION['username'];

// Truy vấn để lấy thông tin người dùng từ cơ sở dữ liệu
$query = "SELECT id, created_at FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $createdAt = $user['created_at'];
} else {
    // Xử lý nếu không tìm thấy người dùng
    $userId = null;
    $createdAt = null;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> <!-- Cấm phóng to, thu nhỏ -->
    <title>Thông tin người dùng</title>
    <!-- Nhúng font Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* CSS không thay đổi */
        @keyframes gradient-animation {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        html, body {
            height: 100%; /* Đảm bảo chiều cao đầy đủ */
            margin: 0;
            overflow: hidden; /* Không cho phép kéo xuống */
            user-select: none; /* Cấm sao chép nội dung */
        }

        body {
            font-family: 'Poppins', sans-serif; /* Thay đổi font ở đây */
            background: linear-gradient(135deg, #ffafbd, #ffc3a0); /* Gradient đẹp cho background */
            background-size: 300% 300%; /* Để hiệu ứng gradient chuyển động */
            animation: gradient-animation 5s ease infinite; /* Thêm animation cho gradient */
            display: flex; /* Để căn giữa container */
            align-items: center; /* Căn giữa theo chiều dọc */
            justify-content: center; /* Căn giữa theo chiều ngang */
        }

        .container {
            max-width: 600px;
            width: 80%; /* Đặt chiều rộng khung chiếm 80% màn hình */
            margin: auto;
            padding: 40px; /* Tăng padding để khung dài hơn */
            background: white; /* Nền trắng cho khung */
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative; /* Để thêm viền gradient */
            z-index: 1; /* Để viền nằm dưới cùng */
            overflow: hidden; /* Ẩn các phần ra ngoài khung */
        }

        .container::before {
            content: "";
            position: absolute;
            top: -5px; /* Đặt viền lên trên khung */
            left: -5px; /* Đặt viền bên trái */
            right: -5px; /* Đặt viền bên phải */
            bottom: -5px; /* Đặt viền bên dưới */
            background: linear-gradient(135deg, azure, lightblue); /* Gradient cho viền */
            border-radius: 8px; /* Để viền mềm mại */
            z-index: -1; /* Đặt viền ở dưới khung */
        }

        @keyframes gradientFade {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            text-align: left; /* Căn trái cho tiêu đề */
            background: linear-gradient(270deg, #ff0080, #ff8c00, #80ff00, #00ff8c, #008cff, #8000ff, #ff0080);
            background-size: 400% 400%; /* Để gradient lớn hơn để có hiệu ứng di chuyển */
            animation: gradientFade 3s ease infinite; /* Thay đổi thời gian để điều chỉnh tốc độ */
            -webkit-background-clip: text; /* Clip nền vào văn bản */
            -webkit-text-fill-color: transparent; /* Làm cho màu chữ trong suốt */
        }

        .user-info {
            margin-top: 20px;
            font-size: 18px;
            line-height: 1.6;
            color: #555;
        }

        .user-info p {
            margin: 10px 0;
            display: flex; /* Sử dụng Flexbox để căn chỉnh */
            justify-content: space-between; /* Đặt không gian giữa hai phần tử */
            align-items: center; /* Căn giữa theo chiều dọc */
        }

        .user-info span {
            width: 50%; /* Đảm bảo phần tiêu đề chiếm 50% chiều rộng */
        }

        .user-info strong {
            color: #007bff; /* Màu xanh cho thông tin quan trọng */
            text-align: right; /* Căn lề phải cho nội dung */
            width: 50%; /* Đảm bảo nội dung chiếm 50% chiều rộng */
        }

        .line {
            border-top: 2px dashed red; /* Đường kẻ đứt màu đỏ */
            margin: 10px 0; /* Khoảng cách với nội dung trước đó */
            width: 100%; /* Chiều rộng đầy đủ */
        }

        .button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3; /* Màu tối hơn khi hover */
        }

        /* CSS cho nút "Click vào đây" */
        .btn-red {
            display: inline-block;
            padding: 10px 20px; /* Padding cho nút */
            background-color: red; /* Màu nền đỏ */
            color: white; /* Màu chữ trắng */
            text-align: center; /* Căn giữa chữ */
            border: none; /* Không viền */
            border-radius: 5px; /* Bo góc */
            font-size: 16px; /* Kích thước chữ */
            cursor: pointer; /* Con trỏ khi hover */
            text-decoration: none; /* Không gạch chân */
        }

        .btn-red:hover {
            background-color: darkred; /* Màu tối hơn khi hover */
        }
    </style>
    <script>
        // Cấm sao chép nội dung
        document.addEventListener('contextmenu', event => event.preventDefault());
        document.addEventListener('copy', event => event.preventDefault());
    </script>
</head>
<body>

<div class="container">
    <h1>THÔNG TIN TÀI KHOẢN</h1>
    <div class="user-info">
        <p><span>Tên người dùng:</span> <strong><?php echo htmlspecialchars($username); ?></strong></p>
        <div class="line"></div>
        <p><span>ID:</span> <strong><?php echo htmlspecialchars($userId); ?></strong></p>
        <div class="line"></div>
        <p><span>Ngày tạo:</span> <strong><?php echo htmlspecialchars(date('d-m-Y H:i:s', strtotime($createdAt))); ?></strong></p>
        <div class="line"></div>
        <p>
            <span>Đổi mật khẩu:</span>
            <strong>
                <a href="change_password.php" class="btn-red">Click vào đây</a>
            </strong>
        </p>
        <div class="line"></div>
        <button class="button" onclick="window.location.href='index.php'">Trang chủ</button>
    </div>
</div>

</body>
</html>