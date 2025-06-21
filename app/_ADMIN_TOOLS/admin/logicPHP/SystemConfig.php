<?php
// Gọi đúng file giải mã
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/DecryptAES.php';

// Lấy dữ liệu mã hóa từ DB và giải mã trước khi hiển thị
$sql = "SELECT * FROM misc WHERE id = 1";
$result = $conn->query($sql);
$system_config = $result->fetch_assoc();

// Giải mã từng field (chỉ khi có dữ liệu)
if ($system_config && isset($system_config['turnstile_site_key'])) {
    $system_config['turnstile_site_key'] = decryptDataAES($system_config['turnstile_site_key']);
    $system_config['turnstile_api_key']  = decryptDataAES($system_config['turnstile_api_key']);
    $system_config['ipinfo_api_key']     = decryptDataAES($system_config['ipinfo_api_key']);
}

// Kiểm tra nếu form được submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/EncryptAES.php';

    $title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $info = htmlspecialchars($_POST['info'], ENT_QUOTES, 'UTF-8');
    $turnstile_site_key = encryptDataAES(htmlspecialchars($_POST['turnstile_site_key'], ENT_QUOTES, 'UTF-8'));
    $turnstile_api_key  = encryptDataAES(htmlspecialchars($_POST['turnstile_api_key'], ENT_QUOTES, 'UTF-8'));
    $ipinfo_api_key     = encryptDataAES(htmlspecialchars($_POST['ipinfo_api_key'], ENT_QUOTES, 'UTF-8'));

    $update_sql = "UPDATE misc SET title = ?, name = ?, info = ?, turnstile_site_key = ?, turnstile_api_key = ?, ipinfo_api_key = ? WHERE id = 1";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssss", $title, $name, $info, $turnstile_site_key, $turnstile_api_key, $ipinfo_api_key);

    if ($stmt->execute()) {
        header('Location: admin.php?section=system_config');
        exit;
    } else {
        echo "Lỗi: " . $stmt->error;
    }
    $stmt->close();
}
?>
