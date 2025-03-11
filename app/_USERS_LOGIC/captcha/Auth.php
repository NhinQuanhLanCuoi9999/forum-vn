<?php
include '../config.php';
session_start();

// Kiểm tra nếu config.php không tồn tại
if (!file_exists('../config.php')) {
    header("Location: ../setup.php"); // Chuyển hướng đến setup.php nếu config.php không tồn tại
    exit();
}

// Truy vấn lấy hcaptcha_api_key và sitekey từ bảng `misc`
$query = "SELECT hcaptcha_api_key, hcaptcha_site_key FROM misc LIMIT 1";
$result = $conn->query($query);

// Kiểm tra nếu có bản ghi
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hcaptcha_api_key = $row['hcaptcha_api_key'];
    $sitekey = $row['hcaptcha_site_key'];
} else {
    die("Không tìm thấy thông tin hCaptcha trong cơ sở dữ liệu.");
}

// Kiểm tra nếu người dùng đã xác thực captcha bằng session
if (isset($_SESSION['captcha_verified'])) {
    header("Location: index.php"); // Nếu đã xác thực, chuyển hướng về trang chính
    exit();
}

$error = ""; 
$successMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra response từ hCaptcha có tồn tại không
    $hcaptcha_response = $_POST['h-captcha-response'] ?? null;

    if (empty($hcaptcha_response)) {
        $error = "Bạn chưa xác minh captcha."; 
    } else {
        // Xác thực với API hCaptcha
        $url = 'https://hcaptcha.com/siteverify';
        $data = [
            'secret' => $hcaptcha_api_key, 
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
        $result_data = json_decode($result, true); // Chuyển thành array để tránh lỗi

        if (!empty($result_data['success']) && $result_data['success'] === true) {
            // Tạo hash ngẫu nhiên và lưu vào session
            $_SESSION['captcha_verified'] = bin2hex(random_bytes(32)); 
            $successMessage = "Xác minh thành công."; 
            header("refresh:3;url=index.php"); // Chuyển hướng sau 3 giây
        } else {
            $error = "Xác thực hCaptcha thất bại. Vui lòng thử lại.";
        }
    }
}
?>
