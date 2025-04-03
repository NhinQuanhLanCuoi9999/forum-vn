<?php
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_google"])) {
    $google_client_id     = $_POST["google_client_id"];
    $google_client_secret = $_POST["google_client_secret"];

    // Chuẩn bị câu lệnh UPDATE
    $stmt = $conn->prepare("UPDATE misc SET google_client_id = ?, google_client_secret = ?");
    if (!$stmt) {
        $message = '<div class="alert alert-danger">Prepare failed: ' . $conn->error . '</div>';
    } else {
        $stmt->bind_param("ss", $google_client_id, $google_client_secret);
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Cập nhật thành công!</div>';
        } else {
            $message = '<div class="alert alert-danger">Execute failed: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    }
}

// Lấy dữ liệu hiện tại từ DB
$sql = "SELECT google_client_id, google_client_secret FROM misc LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $google_client_id     = isset($row['google_client_id']) ? $row['google_client_id'] : '';
    $google_client_secret = isset($row['google_client_secret']) ? $row['google_client_secret'] : '';
} else {
    $google_client_id     = '';
    $google_client_secret = '';
}
?>