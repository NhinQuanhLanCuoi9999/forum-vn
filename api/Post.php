<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/PostApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/ApiResponse.php';

use App\_API\PostApi;
use App\_API\core\ApiResponse;

// Đảm bảo kết nối sử dụng đúng charset
$conn->set_charset('utf8mb4');

$api_key = $_GET['api'] ?? null;
ApiResponse::requireApiKey($api_key);

$postApi = new PostApi($conn, $api_key);

list($valid, $error) = $postApi->validateApiKey();
ApiResponse::check($valid, $error);

list($allowed, $error) = $postApi->rateLimit();
ApiResponse::check($allowed, $error);

$postApi->decrementApiKey();

$username = $_GET['username'] ?? null;
$description = $_GET['description'] ?? null;
$content = $_GET['content'] ?? null;
$sort = $_GET['sort'] ?? 'id:desc';

$posts = $postApi->getPosts($username, $description, $content, $sort);
ApiResponse::checkEmpty($posts);

$conn->close();
echo json_encode($posts, JSON_UNESCAPED_UNICODE);
