<?php
function setupDatabase($host, $user, $pass, $db) {
    $conn = new mysqli($host, $user, $pass);
    if ($conn->connect_error) {
       throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $db") === TRUE) {
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
        // Xử lý kết quả trả về của multi_query
        do {
            if ($result = $conn->store_result()) {
               $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
    }
}

function setupAdmin($conn, $adminPass) {
    // Xóa tài khoản admin nếu tồn tại trước đó
    $conn->query("DELETE FROM users WHERE username = 'admin'");
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'owner')");
    $adminName = 'admin';
    $stmt->bind_param("ss", $adminName, $adminPass);
    $stmt->execute();
    $stmt->close();
}

function setupMisc($conn, $data) {
    $stmt = $conn->prepare("INSERT INTO misc (id, title, name, hcaptcha_api_key, hcaptcha_site_key, ipinfo_api_key, account_smtp, password_smtp)
                            VALUES (1, ?, ?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE title = VALUES(title), name = VALUES(name), 
                            hcaptcha_api_key = VALUES(hcaptcha_api_key), hcaptcha_site_key = VALUES(hcaptcha_site_key),
                            ipinfo_api_key = VALUES(ipinfo_api_key), account_smtp = VALUES(account_smtp), 
                            password_smtp = VALUES(password_smtp)");
    $stmt->bind_param("sssssss", $data['title'], $data['name'], $data['hcaptcha_api_key'], $data['hcaptcha_site_key'], $data['ipinfo_api_key'], $data['smtp_account'], $data['smtp_password']);
    $stmt->execute();
    $stmt->close();
}
?>
