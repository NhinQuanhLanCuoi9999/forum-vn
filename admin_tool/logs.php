<?php
session_start();
include '../config.php';
include $_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/logs/LogicPHP/Read.php';
include($_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/admin/logicPHP/Auth.php');
include($_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/admin/logicPHP/Check2FA.php');


// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
   die("Bạn cần quyền Owner để vào xem logs.");
    exit();
}


?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>
    <link rel="stylesheet" href="../asset/css/Poppins.css">
    <link rel="stylesheet" type="text/css" href="../app/_ADMIN_TOOLS/logs/styles.css">
</head>
<body>

<div class="header">
    <!-- Nút về trang Admin -->
    <a href="admin_tool/admin.php">Về trang Admin</a>
</div>

<div class="container">
    <h1>Log Viewer</h1>

    <!-- Form để chọn file log -->
    <form method="GET" action="">
        <label for="log">Chọn file log:</label>
        <select name="log" id="log" onchange="this.form.submit()">
            <?php foreach ($availableLogs as $log): ?>
                <option value="<?= htmlspecialchars($log) ?>" <?= $selectedLog === $log ? 'selected' : '' ?>>
                    <?= htmlspecialchars($log) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="in4">
        <h2>Thông tin về Log: <?= htmlspecialchars($selectedLog) ?></h2>
        <pre id="log-output" class="log-output">
     <?php   include $_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/logs/LogicPHP/Output.php'; ?>
        </pre>
    </div>
</div>

</body>
</html>
