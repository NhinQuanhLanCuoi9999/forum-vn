<?php
ob_start();
include '../config.php';
session_start();

// Check nếu chưa setup
if (!file_exists('../config.php')) {
    header("Location: ../setup.php");
    exit();
}

// ✅ GỌI FILE GIẢI MÃ + ENV KEY
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/AES_env.php';        // Thêm file này
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/DecryptAES.php';

// ✅ Lấy AES Key
$key = getAESKey();

// Truy vấn lấy API Key đã mã hóa
$query = "SELECT turnstile_api_key, turnstile_site_key FROM misc LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // GIẢI MÃ trước khi dùng
    $turnstile_api_key = decryptDataAES($row['turnstile_api_key']);
    $sitekey = decryptDataAES($row['turnstile_site_key']);


    if (!$turnstile_api_key || !$sitekey) {
        die("Không thể giải mã thông tin Turnstile.");
    }
} else {
    die("Không tìm thấy thông tin Turnstile trong cơ sở dữ liệu.");
}

// Nếu đã verify captcha → chuyển hướng
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
