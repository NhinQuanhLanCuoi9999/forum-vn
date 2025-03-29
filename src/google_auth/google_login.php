<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

use Google\Auth\OAuth2;
use GuzzleHttp\Client as GuzzleClient;

// Lấy Google Client ID & Secret từ database
$sql = "SELECT google_client_id, google_client_secret FROM misc WHERE id = 1 LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $googleClientId = $row['google_client_id'];
    $googleClientSecret = $row['google_client_secret'];
} else {
    die("Lỗi: Không tìm thấy thông tin Google OAuth trong DB");
}

// Kiểm tra xem HTTP_HOST có tồn tại không, nếu không thì mặc định là 'localhost'
$host = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Xác định giao thức: nếu HTTPS thì dùng https, ngược lại là http
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";

// Đường dẫn tuyệt đối đến google_callback.php trong thư mục src/google_auth
$redirectUri = "{$protocol}://{$host}/src/google_auth/google_callback.php";

// Cấu hình OAuth2
$oauth2 = new OAuth2([
    'clientId'            => $googleClientId,
    'clientSecret'        => $googleClientSecret,
    'redirectUri'         => $redirectUri,
    'authorizationUri'    => 'https://accounts.google.com/o/oauth2/auth',
    'tokenCredentialUri'  => 'https://oauth2.googleapis.com/token',
    'scope'               => ['email', 'profile']
]);

// Tạo URL đăng nhập Google
$authUrl = $oauth2->buildFullAuthorizationUri();

// Chuyển hướng người dùng đến trang xác thực của Google
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();
?>