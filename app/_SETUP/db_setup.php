<?php
// Load hàm đọc key từ .env trước
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/AES_env.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/EncryptAES.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/DecryptAES.php';

function setupDatabase($host, $user, $pass, $db) {
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $db)) {
        throw new Exception("Tên database không hợp lệ. Chỉ được dùng chữ cái, số và dấu gạch dưới.");
    }

    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }

    $escapedDb = "`" . $conn->real_escape_string($db) . "`";

    if ($conn->query("CREATE DATABASE IF NOT EXISTS $escapedDb") === TRUE) {
        $conn->select_db($db);
    } else {
        throw new Exception("Không thể tạo cơ sở dữ liệu: " . $conn->error);
    }

    return $conn;
}

function setupSQL($conn, $sqlFile) {
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        if (!$conn->multi_query($sql)) {
            throw new Exception("Lỗi khi chạy SQL: " . $conn->error);
        }
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }
}

function setupAdmin($conn, $adminPass) {
    $conn->query("DELETE FROM users WHERE username = 'admin'");
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'owner')");
    $adminName = 'admin';
    $stmt->bind_param("ss", $adminName, $adminPass);
    $stmt->execute();
    $stmt->close();
}

function setupMisc($conn, $data) {
    $encrypted = [
        'turnstile_api_key'    => encryptDataAES($data['turnstile_api_key']),
        'turnstile_site_key'   => encryptDataAES($data['turnstile_site_key']),
        'ipinfo_api_key'       => encryptDataAES($data['ipinfo_api_key']),
        'smtp_account'         => encryptDataAES($data['smtp_account']),
        'smtp_password'        => encryptDataAES($data['smtp_password']),
        'google_client_id'     => encryptDataAES($data['google_client_id']),
        'google_client_secret' => encryptDataAES($data['google_client_secret']),
    ];

    $stmt = $conn->prepare("INSERT INTO misc (id, title, name, turnstile_api_key, turnstile_site_key, ipinfo_api_key, account_smtp, password_smtp, google_client_id, google_client_secret)
                            VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE title = VALUES(title), name = VALUES(name), 
                            turnstile_api_key = VALUES(turnstile_api_key), turnstile_site_key = VALUES(turnstile_site_key),
                            ipinfo_api_key = VALUES(ipinfo_api_key), account_smtp = VALUES(account_smtp), 
                            password_smtp = VALUES(password_smtp), google_client_id = VALUES(google_client_id), 
                            google_client_secret = VALUES(google_client_secret)");
    $stmt->bind_param(
        "sssssssss",
        $data['title'],
        $data['name'],
        $encrypted['turnstile_api_key'],
        $encrypted['turnstile_site_key'],
        $encrypted['ipinfo_api_key'],
        $encrypted['smtp_account'],
        $encrypted['smtp_password'],
        $encrypted['google_client_id'],
        $encrypted['google_client_secret']
    );
    $stmt->execute();
    $stmt->close();
}
?>
