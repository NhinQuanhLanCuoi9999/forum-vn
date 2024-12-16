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