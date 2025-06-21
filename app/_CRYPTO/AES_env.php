<?php
function getAESKey() {
    $envPath = $_SERVER['DOCUMENT_ROOT'] . '/.env';

    // Nếu file .env chưa tồn tại → tạo mới với AES_KEY
    if (!file_exists($envPath)) {
        $key = random_bytes(32);
        file_put_contents($envPath, "AES_KEY=" . bin2hex($key) . "\n");
        return $key;
    }

    // Đọc nội dung file .env
    $envContent = file_get_contents($envPath);

    // Nếu đã có AES_KEY → lấy và trả về
    if (preg_match('/AES_KEY=([a-f0-9]{64})/', $envContent, $matches)) {
        return hex2bin($matches[1]);
    }

    // Nếu chưa có AES_KEY → thêm vào cuối file
    $key = random_bytes(32);
    $hexKey = bin2hex($key);
    file_put_contents($envPath, $envContent . "\nAES_KEY=$hexKey\n");
    return $key;
}
?>
