<?php
if (isset($_GET['unban'])) {
    $ban_id = $_GET['unban'];
    $stmt = $conn->prepare("DELETE FROM bans WHERE id = ?");
    $stmt->bind_param("i", $ban_id);
    $stmt->execute();

    $success_message = "Đã hủy cấm thành công.";
    writeLog("Hủy cấm ID: $ban_id");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$bans = $conn->query("SELECT bans.*, users.username FROM bans LEFT JOIN users ON bans.username = users.username");
?>