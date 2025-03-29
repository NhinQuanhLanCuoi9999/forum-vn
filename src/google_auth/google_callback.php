<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config.php'; // Kết nối DB có sẵn

use Google\Auth\OAuth2;
use Google\Auth\HttpHandler\Guzzle6HttpHandler;
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

// Tạo redirect URI tuyệt đối
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = rtrim($protocol . "://" . $host . $scriptPath, '/') . "/google_callback.php";

// Cấu hình OAuth2
$oauth2 = new OAuth2([
    'clientId'        => $googleClientId,
    'clientSecret'    => $googleClientSecret,
    'redirectUri'     => $baseUrl,
    'authorizationUri'=> 'https://accounts.google.com/o/oauth2/auth',
    'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
    'scope'           => ['email', 'profile']
]);

// Kiểm tra mã `code` từ Google
if (!isset($_GET['code'])) {
    die("Google login failed!");
}

// Đổi `code` lấy access token
$oauth2->setCode($_GET['code']);
$httpHandler = new Guzzle6HttpHandler(new GuzzleClient());
$accessToken = $oauth2->fetchAuthToken($httpHandler);

if (!isset($accessToken['access_token'])) {
    die("Failed to get access token");
}

// Lấy thông tin người dùng từ Google
$httpClient = new GuzzleClient([
    'headers' => ['Authorization' => 'Bearer ' . $accessToken['access_token']]
]);
$response = $httpClient->get('https://www.googleapis.com/oauth2/v2/userinfo');
$userInfo = json_decode($response->getBody(), true);

$email = $userInfo['email'] ?? null;
$name = $userInfo['name'] ?? null;

if (!$email || !$name) {
    die("Failed to get user info");
}

// Kiểm tra nếu user đã tồn tại
$sql = "SELECT username FROM users WHERE gmail=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Nếu đã có user, đăng nhập luôn
    $row = $result->fetch_assoc();
    $_SESSION['username'] = $row['username'];
} else {
    // Nếu chưa có tài khoản, tạo mới
    $originalName = $name;
    $newName = $originalName;

    // Kiểm tra trùng username
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    if (!$checkStmt) {
        die("Lỗi SQL: " . $conn->error);
    }

    do {
        $checkStmt->bind_param("s", $newName);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->reset();

        if ($count > 0) {
            // Nếu trùng, thêm 5 ký tự hex random
            $newName = $originalName . "_" . substr(bin2hex(random_bytes(3)), 0, 5);
        } else {
            break;
        }
    } while (true);

    $checkStmt->close();

    // Tạo mật khẩu random 32 ký tự hex
    $randomPassword = bin2hex(random_bytes(16));

    // Hash bằng bcrypt (cost = 10)
    $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 10]);

    // INSERT user mới
    $stmt = $conn->prepare("INSERT INTO users (username, gmail, password, is_active) VALUES (?, ?, ?, '1')");
    if (!$stmt) {
        die("Lỗi SQL: " . $conn->error);
    }

    $stmt->bind_param("sss", $newName, $email, $hashedPassword);
    if ($stmt->execute()) {
        $_SESSION['username'] = $newName;
    } else {
        die("Lỗi khi INSERT: " . $stmt->error);
    }
}

$stmt->close();
$conn->close();
header("Location: /");
exit();
?>