<?php
function handleToggleComment($conn) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle'])) {
        toggle_comment($conn, $_POST['toggle']);
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

?>