<?php

// Hàm tạo API key ngẫu nhiên
function generateApiKey($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Hàm ghi log vào file
function writeLog($action, $apiKey, $ipAddress) {
    $logFile = '../logs/api.txt';  // Đảm bảo đường dẫn đúng

    // Kiểm tra nếu file chưa tồn tại thì tạo ra
    if (!file_exists($logFile)) {
        if (!touch($logFile)) {
            echo "Error creating log file.";
            return;
        }
    }

    $dateTime = date('Y-m-d H:i:s');
    $logMessage = "$dateTime - IP: $ipAddress - Action: $action - API Key: $apiKey\n";

    // Ghi log vào file và kiểm tra nếu có lỗi
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        echo "Error writing to log file!";
    }
}

// Thêm API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_key'])) {
    $newApiKey = generateApiKey();
    
    // Kết nối với cơ sở dữ liệu (cần khai báo $conn trước khi sử dụng)
    $stmt = $conn->prepare("INSERT INTO api_keys (api_key, is_active) VALUES (?, 1)");
    $stmt->bind_param("s", $newApiKey);
    if ($stmt->execute()) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        writeLog('API Key Created', $newApiKey, $ipAddress);
        $_SESSION['message'] = "API key created successfully!";
        
        // Redirect lại trang sau 1s để tránh gửi lại biểu mẫu
        header("Refresh:1; url=" . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error creating API key! " . $stmt->error;
    }
}

// Xóa API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_key'])) {
    // Kiểm tra xem API key có được gửi lên không
    if (isset($_POST['api_key']) && !empty($_POST['api_key'])) {
        $apiKeyToDelete = $_POST['api_key'];
    } else {
        echo "API key not provided.";
        exit;
    }

    // Kết nối với cơ sở dữ liệu
    $stmt = $conn->prepare("DELETE FROM api_keys WHERE api_key = ?");
    $stmt->bind_param("s", $apiKeyToDelete);
    
    if ($stmt->execute()) {
        // Kiểm tra xem có bản ghi nào bị xóa không
        if ($stmt->affected_rows > 0) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            writeLog('API Key Deleted', $apiKeyToDelete, $ipAddress);
            $_SESSION['message'] = "API key deleted successfully!";
            
            // Redirect lại trang sau 1s để tránh gửi lại biểu mẫu
            header("Refresh:1; url=" . $_SERVER['PHP_SELF']);
            exit;
        } else {
            echo "No API key found to delete.";
        }
    } else {
        echo "Error deleting API key! " . $stmt->error;  // In thông báo lỗi nếu có
    }
    $stmt->close();  // Đảm bảo đóng kết nối sau khi sử dụng
}
?>
