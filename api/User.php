<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/UserApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/ApiResponse.php';
use App\_API\UserApi;
use App\_API\core\ApiResponse;

$api_key = isset($_GET['api']) ? $_GET['api'] : null;
ApiResponse::requireApiKey($api_key);

$userApi = new UserApi($conn, $api_key);

list($valid, $error) = $userApi->validateApiKey();
ApiResponse::check($valid, $error);

list($allowed, $error) = $userApi->rateLimit();
ApiResponse::check($allowed, $error);

$userApi->decrementApiKey();

$username = $_GET['username'] ?? null;
$desc = $_GET['desc'] ?? null;
$sort = $_GET['sort'] ?? 'id:desc';


$users = $userApi->getUsers($username, $desc, $sort);

ApiResponse::checkEmpty($users);
$conn->close();
echo json_encode($users, JSON_UNESCAPED_UNICODE);
