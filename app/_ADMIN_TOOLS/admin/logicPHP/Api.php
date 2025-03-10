<?php
include '../config.php';

// Hàm tạo API key ngẫu nhiên
function generateApiKey($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Quản lý API key nếu truy cập vào section `api`
$apiKeys = [];
if (isset($_GET['section']) && $_GET['section'] === 'api') {
    // Thêm API key
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_key'])) {
        $newApiKey = generateApiKey();
        $stmt = $conn->prepare("INSERT INTO api_keys (api_key, is_active) VALUES (?, 1)");
        $stmt->bind_param("s", $newApiKey);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "API key created successfully!";
    }

    // Kích hoạt/Vô hiệu hóa API key
    if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
        $id = intval($_GET['toggle']);
        $stmt = $conn->prepare("UPDATE api_keys SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "API key status updated!";
    }

    // Xóa API key
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $id = intval($_GET['delete']);
        $stmt = $conn->prepare("DELETE FROM api_keys WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['message'] = "API key deleted!";
    }

    // Lấy danh sách API keys
    $result = $conn->query("SELECT id, api_key, is_active, created_at FROM api_keys");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $apiKeys[] = $row;
        }
    }
    $conn->close();
}
?>
