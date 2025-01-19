<?php
include '../config.php';
// Khởi tạo session
session_start();

// Kiểm tra nếu config.php không tồn tại
if (!file_exists('../config.php')) {
    header("Location: ../setup.php"); // Chuyển hướng đến setup.php nếu config.php không tồn tại
    exit();
}
// Lấy hcaptcha_api_key từ bảng misc (chỉ lấy 1 bản ghi duy nhất)
$query = "SELECT hcaptcha_api_key FROM misc LIMIT 1";
$result = $conn->query($query);

// Kiểm tra nếu có bản ghi
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hcaptcha_api_key = $row['hcaptcha_api_key']; // Lấy hcaptcha_api_key từ cơ sở dữ liệu
} else {
    die("Không tìm thấy hcaptcha_api_key trong cơ sở dữ liệu.");
}

// Kiểm tra nếu người dùng đã xác thực captcha bằng session
if (isset($_SESSION['captcha_verified'])) {
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
            'secret' => $hcaptcha_api_key, // Sử dụng khóa API từ cơ sở dữ liệu
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
            // Tạo hash ngẫu nhiên và lưu vào session
            $_SESSION['captcha_verified'] = bin2hex(random_bytes(32)); // Lưu hash vào session

            // Thông báo thành công
            $successMessage = "Xác minh thành công."; 
            header("refresh:3;url=index.php"); // Chuyển hướng sau 3 giây
        } else {
            $error = "Xác thực hCaptcha thất bại. Vui lòng thử lại.";
        }
    }
}
?>
