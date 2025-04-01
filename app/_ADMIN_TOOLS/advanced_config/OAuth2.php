<?php

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_google"])) {
    $google_client_id     = $_POST["google_client_id"];
    $google_client_secret = $_POST["google_client_secret"];

    $stmt = $conn->prepare("UPDATE misc SET google_client_id = ?, google_client_secret = ?");
    $stmt->bind_param("ss", $google_client_id, $google_client_secret);
    $stmt->close();
}

$sql = "SELECT google_client_id, google_client_secret FROM misc LIMIT 1";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$google_client_id     = isset($row['google_client_id']) ? $row['google_client_id'] : '';
$google_client_secret = isset($row['google_client_secret']) ? $row['google_client_secret'] : '';

?>