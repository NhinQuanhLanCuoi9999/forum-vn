<?php
header('Content-Type: application/json');
include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/BanApi.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/ApiResponse.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/_API/core/Bearer.php';

use App\_API\BanApi;
use App\_API\core\ApiResponse;
use App\_API\core\Bearer;

$api_key = Bearer::getBearerToken();

$banApi = new BanApi($conn, $api_key);

list($valid, $error) = $banApi->validateApiKey();
ApiResponse::check($valid, $error);

list($allowed, $error) = $banApi->rateLimit();
ApiResponse::check($allowed, $error);

$banApi->decrementApiKey();

$username = $_GET['username'] ?? null;
$ip = $_GET['ip'] ?? null;
$sort = $_GET['sort'] ?? 'id:desc';

$bans = $banApi->getBans($username, $ip, $sort);

ApiResponse::checkEmpty($bans);
$conn->close();
echo json_encode($bans, JSON_UNESCAPED_UNICODE);
