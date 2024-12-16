<?php
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
$apiKeys = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $apiKeys[] = $row;
    }
}
?>