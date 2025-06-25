<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/CommentApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/ApiResponse.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/Bearer.php';

use App\_API\CommentApi;
use App\_API\core\ApiResponse;
use App\_API\core\Bearer;

$api_key = Bearer::getBearerToken();

$commentApi = new CommentApi($conn, $api_key);

list($valid, $error) = $commentApi->validateApiKey();
ApiResponse::check($valid, $error);

list($allowed, $error) = $commentApi->rateLimit();
ApiResponse::check($allowed, $error);

$commentApi->decrementApiKey();


$post_id  = $_GET['post_id']  ?? null;
$username = $_GET['username'] ?? null;
$content  = $_GET['content']  ?? null;
$sort     = $_GET['sort']     ?? 'id:desc';


$comments = $commentApi->getComments($post_id, $username, $content, $sort);

ApiResponse::checkEmpty($comments);
$conn->close();
echo json_encode($comments, JSON_UNESCAPED_UNICODE);
