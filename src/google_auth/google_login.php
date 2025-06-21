<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/DecryptAES.php';

use Google\Auth\OAuth2;

// Truy vấn & giải mã Google Client ID & Secret
$sql = "SELECT google_client_id, google_client_secret FROM misc WHERE id = 1 LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $googleClientId = decryptDataAES($row['google_client_id']);
    $googleClientSecret = decryptDataAES($row['google_client_secret']);
} else {
    die("Lỗi: Không tìm thấy thông tin Google OAuth trong DB");
}

// Giao thức & host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Redirect URI
$redirectUri = "{$protocol}://{$host}/src/google_auth/google_callback.php";

// Cấu hình OAuth2
$oauth2 = new OAuth2([
    'clientId'           => $googleClientId,
    'clientSecret'       => $googleClientSecret,
    'redirectUri'        => $redirectUri,
    'authorizationUri'   => 'https://accounts.google.com/o/oauth2/auth',
    'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
    'scope'              => ['email', 'profile']
]);

// Build URL và redirect
$authUrl = $oauth2->buildFullAuthorizationUri(['state' => 'popup']);
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit();
?>
