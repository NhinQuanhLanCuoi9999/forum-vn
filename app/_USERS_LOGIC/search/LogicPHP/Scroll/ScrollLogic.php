<?php
function getSearchParameters() {
    return [
        'search' => isset($_GET['search']) ? $_GET['search'] : '',
        'start_date' => isset($_GET['start_date']) ? $_GET['start_date'] : '',
        'end_date' => isset($_GET['end_date']) ? $_GET['end_date'] : '',
        'page' => isset($_GET['page']) ? (int)$_GET['page'] : 1
    ];
}

function getScrollParameters($page, $per_page) {
    return [
        'per_page' => $per_page,
        'start_limit' => ($page - 1) * $per_page
    ];
}
?>