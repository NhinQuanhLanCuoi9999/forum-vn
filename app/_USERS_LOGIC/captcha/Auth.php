<?php

ob_start();
include '../config.php';
session_start();

// Kiểm tra nếu config.php không tồn tại
if (!file_exists('../config.php')) {
    header("Location: ../setup.php");
    exit();
}

// Truy vấn lấy API Key từ DB
$query = "SELECT turnstile_api_key, turnstile_site_key FROM misc LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $turnstile_api_key = $row['turnstile_api_key'];
    $sitekey = $row['turnstile_site_key'];
} else {
    die("Không tìm thấy thông tin Turnstile trong cơ sở dữ liệu.");
}

// Nếu session captcha_verified đã tồn tại, chuyển hướng ngay
if (isset($_SESSION['captcha_verified'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turnstile_response = $_POST['cf-turnstile-response'] ?? null;

    if (!empty($turnstile_response)) {
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
        $data = [
            'secret'   => $turnstile_api_key,
            'response' => $turnstile_response,
            'remoteip' => $_SERVER['REMOTE_ADDR']
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $result_data = json_decode($result, true);

        if (!empty($result_data['success']) && $result_data['success'] === true) {
            $_SESSION['captcha_verified'] = true;
            header("Location: index.php");
            exit();
        } else {
            $error = "Xác thực thất bại. Vui lòng thử lại.";
        }
    } else {
        $error = "Captcha không hợp lệ. Vui lòng thử lại.";
    }
}
ob_end_flush();
?>