<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_CRYPTO/DecryptAES.php';

// Lấy & giải mã API key
$query = "SELECT ipinfo_api_key FROM misc LIMIT 1";
$result = $conn->query($query);
$apiKey = null;

if ($result && $result->num_rows > 0) {
    $encryptedKey = $result->fetch_assoc()['ipinfo_api_key'];
    $apiKey = decryptDataAES($encryptedKey);
}

// Lấy thống kê
$totalUsers = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'];
$latestUser = $conn->query("SELECT username FROM users ORDER BY created_at DESC LIMIT 1")->fetch_assoc()['username'];
$totalPosts = $conn->query("SELECT COUNT(*) AS total FROM posts")->fetch_assoc()['total'];
$latestPost = $conn->query("SELECT id, content FROM posts ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
?>
