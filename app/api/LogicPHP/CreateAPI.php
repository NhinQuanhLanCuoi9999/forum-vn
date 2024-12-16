<?php
// Hàm tạo API key ngẫu nhiên
function generateApiKey($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Thêm API key
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_key'])) {
    $newApiKey = generateApiKey();
    $stmt = $conn->prepare("INSERT INTO api_keys (api_key, is_active) VALUES (?, 1)");
    $stmt->bind_param("s", $newApiKey);
    $stmt->execute();
    $stmt->close();
    $_SESSION['message'] = "API key created successfully!";
}
?>