<?php
session_start();
header('Content-Type: application/json');


include '../config.php';

$sql = "SELECT id, username, created_at,`desc` FROM users";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

$conn->close();
echo json_encode($users);
?>