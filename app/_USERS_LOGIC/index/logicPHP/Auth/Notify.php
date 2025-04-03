<?php

$misc_name = '';

if (isset($_SESSION['username'])) {
    $query = "SELECT info FROM misc LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $misc_name = isset($row['info']) && trim($row['info']) !== '' ? htmlspecialchars($row['info'], ENT_QUOTES, 'UTF-8') : '';
    }
}
?>
