<?php
include $_SERVER['DOCUMENT_ROOT'] . '/app/_USERS_LOGIC/search/LogicPHP/Scroll/ScrollLogic.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_USERS_LOGIC/search/LogicPHP/Handle/FetchPost.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_USERS_LOGIC/search/LogicPHP/Handle/RenderPost.php';

include $_SERVER['DOCUMENT_ROOT'] . '/config.php';
function handleAjaxRequest($result) {
    ob_start();
    renderPosts($result);
    echo ob_get_clean();
    exit;
}

$params = getSearchParameters();
$Scroll = getScrollParameters($params['page'], 5);
$result = fetchPosts($conn, $params['search'], $params['start_date'], $params['end_date'], $Scroll['start_limit'], $Scroll['per_page']);

if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    handleAjaxRequest($result);
}
?>