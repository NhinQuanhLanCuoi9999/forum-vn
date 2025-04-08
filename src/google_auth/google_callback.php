<?php
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/config.php';

use Google\Auth\OAuth2;
use Google\Auth\HttpHandler\Guzzle6HttpHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

function showErrorAndRedirect($message, $info = 'Hệ thống đang gặp lỗi xác thực , vui lòng quay lại sau',  $code = 'unknown_error', $rawError = null) {
    header('Content-Type: text/html; charset=UTF-8');

    $errorPayload = [
        'error'   => $info,
        'message' => $message,
        'code'    => $code
    ];

    if ($rawError) {
        $decoded = json_decode($rawError, true);
        $errorPayload['raw'] = $decoded ?: $rawError;
    }

    $jsonError = json_encode($errorPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Lỗi xác thực</title>
  <script>
    const errorData = $jsonError;

    window.addEventListener("DOMContentLoaded", () => {
      if (window.opener && !window.opener.closed) {
        window.opener.postMessage({ type: "auth_error", data: errorData }, window.location.origin);
        document.body.innerHTML = "<pre>" + JSON.stringify(errorData, null, 2) + "</pre>";
        setTimeout(() => window.close(), 4000);
      } else {
        document.body.innerHTML = "<pre>" + JSON.stringify(errorData, null, 2) + "</pre>";
        setTimeout(() => window.location.href = "/", 5000);
      }
    });
  </script>
</head>
<body></body>
</html>
HTML;

    exit();
}



// Lấy Client ID + Secret
$sql = "SELECT google_client_id, google_client_secret FROM misc WHERE id = 1 LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $googleClientId = $row['google_client_id'];
    $googleClientSecret = $row['google_client_secret'];
} else {
    showErrorAndRedirect("Lỗi: Không tìm thấy thông tin Google OAuth trong DB");
}

// Tạo redirect URI đúng chuẩn
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$scriptPath = dirname($_SERVER['SCRIPT_NAME']);
$redirectUri = rtrim($protocol . "://" . $host . $scriptPath, '/') . "/google_callback.php";

// Cấu hình OAuth2
$oauth2 = new OAuth2([
    'clientId'         => $googleClientId,
    'clientSecret'     => $googleClientSecret,
    'redirectUri'      => $redirectUri,
    'authorizationUri' => 'https://accounts.google.com/o/oauth2/auth',
    'tokenCredentialUri' => 'https://oauth2.googleapis.com/token',
    'scope'            => ['email', 'profile']
]);

// Phải có mã `code` mới chơi được
if (!isset($_GET['code'])) {
    showErrorAndRedirect("Google login failed!");
}

try {
    $oauth2->setCode($_GET['code']);
    $httpHandler = new Guzzle6HttpHandler(new GuzzleClient());
    $accessToken = $oauth2->fetchAuthToken($httpHandler);

    if (!isset($accessToken['access_token'])) {
        showErrorAndRedirect("Không lấy được access token");
    }

    // Lấy info người dùng
    $httpClient = new GuzzleClient([
        'headers' => ['Authorization' => 'Bearer ' . $accessToken['access_token']]
    ]);
    $response = $httpClient->get('https://www.googleapis.com/oauth2/v2/userinfo');
    $userInfo = json_decode($response->getBody(), true);

} catch (RequestException $e) {
    $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : "Unknown error";
    showErrorAndRedirect("Lỗi OAuth: " . htmlspecialchars($responseBody));
}

$email = $userInfo['email'] ?? null;
$name  = $userInfo['name'] ?? null;

if (!$email || !$name) {
    showErrorAndRedirect("Không lấy được info người dùng");
}

// Kiểm tra user đã tồn tại chưa
$sql = "SELECT username FROM users WHERE gmail=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $_SESSION['username'] = $row['username'];
} else {
    // Tạo user mới
    $originalName = $name;
    $newName = $originalName;

    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    if (!$checkStmt) {
        showErrorAndRedirect("SQL error: " . $conn->error);
    }

    do {
        $checkStmt->bind_param("s", $newName);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->reset();

        if ($count > 0) {
            $newName = $originalName . "_" . substr(bin2hex(random_bytes(3)), 0, 5);
        } else {
            break;
        }
    } while (true);

    $checkStmt->close();

    $randomPassword = bin2hex(random_bytes(16));
    $hashedPassword = password_hash($randomPassword, PASSWORD_BCRYPT, ['cost' => 10]);

    $stmt = $conn->prepare("INSERT INTO users (username, gmail, password, is_active) VALUES (?, ?, ?, '1')");
    if (!$stmt) {
        showErrorAndRedirect("Lỗi SQL: " . $conn->error);
    }

    $stmt->bind_param("sss", $newName, $email, $hashedPassword);
    if ($stmt->execute()) {
        $_SESSION['username'] = $newName;
    } else {
        showErrorAndRedirect("Lỗi khi INSERT: " . $stmt->error);
    }
}

$stmt->close();
$conn->close();

// 🚨 Kiểm tra state để biết có phải popup không
if (isset($_GET['state']) && $_GET['state'] === 'popup') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Đăng nhập thành công</title>
        <script>
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage("auth_success", window.location.origin);
            }
            window.close();
        </script>
    </head>
    <body>
        <p>Đăng nhập thành công! Đang quay về trang chính...</p>
    </body>
    </html>
    <?php
    exit();
} else {
    header("Location: /");
    exit();
}
?>
