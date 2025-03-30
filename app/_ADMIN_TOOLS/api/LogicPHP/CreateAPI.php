<?php

// Hàm tạo API key ngẫu nhiên
function generateApiKey($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Hàm ghi log vào file
function writeLog($action, $apiKey, $ipAddress) {
    $logFile = '../logs/admin/api.txt'; // Đảm bảo đường dẫn đúng
    if (!file_exists($logFile)) {
        if (!touch($logFile)) {
            echo "Error creating log file.";
            return;
        }
    }
    $dateTime = date('Y-m-d H:i:s');
    $logMessage = "$dateTime - IP: $ipAddress - Action: $action - API Key: $apiKey\n";
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        echo "Error writing to log file!";
    }
}

// Thêm API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_key'])) {
    $newApiKey = generateApiKey();
    $stmt = $conn->prepare("INSERT INTO api_keys (api_key, is_active) VALUES (?, 1)");
    $stmt->bind_param("s", $newApiKey);
    if ($stmt->execute()) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        writeLog('API Key Created', $newApiKey, $ipAddress);
        $_SESSION['message'] = "API key created successfully!";
        header("Refresh:1; url=" . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error creating API key! " . $stmt->error;
    }
}

// Xóa API key
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("SELECT api_key FROM api_keys WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $apiKey = $row['api_key'];
        $stmt = $conn->prepare("DELETE FROM api_keys WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        writeLog('API Key Deleted', $apiKey, $ipAddress);
        $_SESSION['message'] = "API key deleted successfully!";

        // Kiểm tra nếu bảng trống và reset auto-increment nếu không có bản ghi nào
        $result = $conn->query("SELECT COUNT(*) AS count FROM api_keys");
        $row = $result->fetch_assoc();
        if ($row['count'] == 0) {
            $conn->query("ALTER TABLE api_keys AUTO_INCREMENT = 1");
        }
    } else {
        $_SESSION['message'] = "API key không tồn tại.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Kích hoạt/Vô hiệu hóa API key
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $stmt = $conn->prepare("SELECT api_key, is_active FROM api_keys WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentStatus = $row['is_active'];
        $apiKey = $row['api_key'];
        $newStatus = ($currentStatus == 1) ? 0 : 1;
        $stmt = $conn->prepare("UPDATE api_keys SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $id);
        $stmt->execute();
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $action = $newStatus == 1 ? 'API Key Activated' : 'API Key Deactivated';
        writeLog($action, $apiKey, $ipAddress);
        $_SESSION['message'] = $newStatus == 1 ? "API key đã được kích hoạt!" : "API key đã được vô hiệu hóa!";
    } else {
        $_SESSION['message'] = "API key không tồn tại.";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Lấy danh sách API keys
$apiKeys = [];
$result = $conn->query("SELECT id, api_key, is_active, created_at FROM api_keys");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $apiKeys[] = $row;
    }
}
?>
