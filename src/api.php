<?php
session_start();
include '../config.php';  // Đảm bảo cấu hình kết nối DB
include '../app/api/php.php';  // Đảm bảo hàm writeLog() và các hàm cần thiết

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý API</title>
    <link rel="stylesheet" type="text/css" href="/app/api/styles.css">
</head>
<body>
    <h1>Quản lý API Keys</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <p class="message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
    <?php endif; ?>

    <form method="POST">
        <button type="submit" name="generate_key" class="btn btn-generate">Tạo API Key mới</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>API Key</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($apiKeys as $key): ?>
                <tr>
                    <td><?= $key['id']; ?></td>
                    <td><?= $key['api_key']; ?></td>
                    <td><?= $key['is_active'] ? 'Kích hoạt' : 'Vô hiệu hóa'; ?></td>
                    <td><?= $key['created_at']; ?></td>
                    <td>
                        <a href="?toggle=<?= $key['id']; ?>" class="btn btn-toggle">
                            <?= $key['is_active'] ? 'Vô hiệu hóa' : 'Kích hoạt'; ?>
                        </a>
                        <a href="?delete=<?= $key['id']; ?>" class="btn btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?');">Xóa</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
