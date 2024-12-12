<?php
session_start();
include('config.php');

// Kiểm tra nếu người dùng chưa đăng nhập, chuyển hướng về index.php
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Kiểm tra nếu người dùng bị cấm
$username = $_SESSION['username'];
$ip = $_SERVER['REMOTE_ADDR'];

$stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
$stmt->bind_param("ss", $username, $ip);
$stmt->execute();
$result = $stmt->get_result();

// Nếu có thông tin về lệnh cấm, chuyển hướng đến warning.php
if ($result->num_rows > 0) {
    header("Location: warning.php");
    exit();
}

// Kiểm tra captcha
if (!isset($_COOKIE['captcha_verified']) || $_COOKIE['captcha_verified'] != 'true') {
    header("Location: captcha_verification.php");
    exit();
}

// Xử lý thay đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $username = $_SESSION['username'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu mới với xác nhận
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
        header("Location: change_password.php");
        exit();
    }

    // Lấy mật khẩu hiện tại từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($current_password, $user['password'])) {
        // Mã hóa mật khẩu mới
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Cập nhật mật khẩu trong cơ sở dữ liệu
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashed_password, $username);
        $stmt->execute();
        
        // Thông báo thành công và chuyển hướng lại change_password.php
        $_SESSION['success'] = "Mật khẩu đã được thay đổi thành công!";
        header("Location: change_password.php");
        exit();
    } else {
        $_SESSION['error'] = "Mật khẩu hiện tại không đúng!";
        header("Location: change_password.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet"> <!-- Thêm font Poppins -->
    <style>
        /* Toàn bộ trang */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; /* Sử dụng font Poppins */
        }

        body {
            background: linear-gradient(to bottom, #4A90E2, #50E3C2); /* Gradient nền */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden; /* Ngăn không cho lướt kéo trang */
        }

    .container {
    background: linear-gradient(to bottom, #ffffff, #f2f2f2); /* Gradient cho khung form */
    padding: 40px; /* Tăng padding lên để làm cho form lớn hơn */
    border-radius: 8px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px; /* Tăng max-width để mở rộng chiều rộng của form */
    opacity: 0; /* Ẩn khi tải trang */
    transform: translateY(20px); /* Đẩy nhẹ xuống */
    animation: fadeInUp 0.5s forwards; /* Thêm hiệu ứng fade-in */
}

input[type="password"] {
    width: 100%;
    padding: 12px; /* Tăng padding cho các trường nhập để làm cho chúng lớn hơn */
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.3s ease; /* Thêm hiệu ứng cho border */
    height: 50px; /* Thêm chiều cao cụ thể cho trường nhập */
}

        @keyframes fadeInUp {
            to {
                opacity: 1; /* Hiện thị đầy đủ */
                transform: translateY(0); /* Trở về vị trí ban đầu */
            }
        }

        /* Tiêu đề */
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

h2 {
    text-align: left;
    margin-bottom: 20px;
    background: linear-gradient(270deg, #ff0000, #ff7f00, #ffff00, #7fff00, #00ff7f, #00ffff, #007fff);
    background-size: 400% 400%; /* Kích thước nền để có hiệu ứng chuyển động */
    -webkit-background-clip: text;
    color: transparent;
    animation: gradientFade 3s ease infinite; /* Tăng tốc độ chuyển động ở đây */
    font-size: 2em; /* Tùy chỉnh kích thước chữ nếu cần */
}
        /* Thông báo lỗi và thành công */
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            opacity: 0; /* Ẩn khi tải */
            transform: translateY(-20px); /* Đẩy nhẹ lên */
            animation: slideIn 0.5s forwards; /* Thêm hiệu ứng slide-in */
        }

        @keyframes slideIn {
            0% {
                opacity: 0; /* Bắt đầu ẩn */
                transform: translateY(-20px); /* Bắt đầu từ vị trí trên */
            }
            100% {
                opacity: 1; /* Hiện thị đầy đủ */
                transform: translateY(0); /* Trở về vị trí ban đầu */
            }
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            animation: slideIn 0.5s forwards; /* Thêm hiệu ứng slide-in cho thông báo lỗi */
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        /* Các trường nhập dữ liệu */
        label {
            font-size: 16px;
            margin-bottom: 8px;
            display: block;
            color: #333;
        }

   

        input[type="password"]:focus {
            border-color: #007bff;
        }

        /* Nút submit */
        .submit-btn {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, transform 0.3s ease; /* Thêm hiệu ứng cho nút */
        }

        .submit-btn:hover {
            background-color: #218838;
            transform: scale(1.05); /* Tăng kích thước nhẹ khi hover */
        }

        .submit-btn:active {
            background-color: #1e7e34;
        }

        /* Liên kết quay lại */
        p {
            text-align: center;
            margin-top: 15px;
        }

        a {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #0056b3; /* Màu sắc khi hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>ĐỔI MẬT KHẨU</h2>

        <!-- Hiển thị thông báo lỗi nếu có -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Hiển thị thông báo thành công nếu có -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <script>
                // Sau khi hiển thị thông báo thành công, chuyển hướng sau 3 giây
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 3000); // 3000 ms = 3 giây
            </script>
        <?php endif; ?>

        <!-- Form nhập mật khẩu -->
        <form action="change_password.php" method="POST">
            <label for="current_password">Mật khẩu cũ:</label>
            <input type="password" name="current_password" required>

            <label for="new_password">Mật khẩu mới:</label>
            <input type="password" name="new_password" required minlength="6" maxlength="30">

            <label for="confirm_password">Xác nhận mật khẩu mới:</label>
            <input type="password" name="confirm_password" required minlength="6" maxlength="30">
            <p>Chưa muốn đổi mật khẩu? <a href="index.php">Quay lại</a>.</p>
            <button type="submit" name="change_password" class="submit-btn">Đổi mật khẩu</button>
        </form>
    </div>
</body>
</html>