<?php
session_start();

// Kiểm tra nếu người dùng đã xác thực captcha bằng cookie
if (isset($_COOKIE['captcha_verified'])) {
    header("Location: index.php"); // Nếu đã xác thực, chuyển hướng về trang chính
    exit();
}

$error = ""; // Khởi tạo biến lỗi
$successMessage = ""; // Khởi tạo biến thông báo thành công

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy response từ hCaptcha
    $hcaptcha_response = $_POST['h-captcha-response'];

    // Kiểm tra xem hCaptcha response có tồn tại không
    if (!$hcaptcha_response) {
        $error = "Bạn chưa xác minh captcha."; // Thông báo lỗi
    } else {
        // Xác thực với API hCaptcha
        $url = 'https://hcaptcha.com/siteverify';
        $data = [
            'secret' => 'ES_eafd0df71344421686007fe53757d82a', // Thay thế bằng khóa bí mật của bạn từ hCaptcha
            'response' => $hcaptcha_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result_data = json_decode($result);

        // Kiểm tra nếu xác thực thành công
        if ($result_data->success) {
            // Lưu cookie đã xác thực, tồn tại trong 30 phút
            setcookie('captcha_verified', 'true', time() + 300000, "/");
            
            $successMessage = "Xác minh thành công."; // Thông báo thành công
            header("refresh:3;url=index.php"); // Chuyển hướng sau 3 giây
        } else {
            $error = "Xác thực hCaptcha thất bại. Vui lòng thử lại.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Xác thực Captcha</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Thiết lập font chữ và reset mặc định */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .captcha-box {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        h2 {
            font-size: 1.8em;
            margin-bottom: 20px;
            color: #333;
        }

        p {
            font-size: 1.1em;
            color: #666;
            margin-bottom: 30px;
        }

        .error-message {
            color: red; /* Màu đỏ cho thông báo lỗi */
            margin-bottom: 15px;
        }

        .success-message {
            color: green; /* Màu xanh cho thông báo thành công */
            margin-bottom: 15px;
        }

        .captcha-container {
            margin-bottom: 20px;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="captcha-box">
            <h2>Xác thực bạn không phải là robot</h2>
            <p>Vui lòng hoàn thành captcha bên dưới để tiếp tục truy cập trang web.</p>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            <form action="captcha_verification.php" method="POST">
                <div class="captcha-container">
                    <div class="h-captcha" data-sitekey="8ab08556-388c-4fca-b4e4-6844ec20b396"></div>
                </div>
                <button type="submit" class="btn-submit">Xác minh</button>
            </form>
        </div>
    </div>

    <!-- Nhúng script hCaptcha -->
    <script src="https://hcaptcha.com/1/api.js" async defer></script>
</body>
</html>