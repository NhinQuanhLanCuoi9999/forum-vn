<?php
session_start();
header('Content-Type: application/json');

include '../config.php';

$sql = "SELECT id,username,ip_address,reason,ban_start,ban_end,permanent FROM bans";
$result = $conn->query($sql);

$comment = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $comment[] = $row;
    }
}

$conn->close();
echo json_encode($comment);
?>