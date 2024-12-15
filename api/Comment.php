<?php
session_start();
header('Content-Type: application/json');

include '../config.php';

$sql = "SELECT id,post_id,content,username,created_at FROM comments";
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