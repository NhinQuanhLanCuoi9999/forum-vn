<?php
session_start();
header('Content-Type: application/json');


include '../config.php';

$sql = "SELECT id, content, username, description, file, created_at FROM posts"; 
$result = $conn->query($sql);

$post = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $post[] = $row;
    }
}

$conn->close();
echo json_encode($post);
?>