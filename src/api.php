<?php
session_start();
include '../config.php';  // Đảm bảo cấu hình kết nối DB
include '../app/api/php.php';  // Đảm bảo hàm writeLog() và các hàm cần thiết

// Lấy danh sách API keys
$apiKeys = [];
$result = $conn->query("SELECT id, api_key, is_active, created_at FROM api_keys");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $apiKeys[] = $row;
    }
}

// Kích hoạt/Vô hiệu hóa API key
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $id = intval($_GET['toggle']);

    // Lấy thông tin trạng thái hiện tại của API key
    $stmt = $conn->prepare("SELECT api_key, is_active FROM api_keys WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Nếu API key tồn tại
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentStatus = $row['is_active'];
        $apiKey = $row['api_key'];

        // Chuyển đổi trạng thái (kích hoạt hoặc vô hiệu hóa)
        $newStatus = ($currentStatus == 1) ? 0 : 1; // Nếu is_active = 1 thì set lại là 0 và ngược lại

        // Cập nhật trạng thái mới cho API key
        $stmt = $conn->prepare("UPDATE api_keys SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStatus, $id);
        $stmt->execute();
        $stmt->close();

        // Thêm log vào file
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $action = $newStatus == 1 ? 'API Key Activated' : 'API Key Deactivated';
        writeLog($action, $apiKey, $ipAddress);

        // Thông báo tương ứng
        $_SESSION['message'] = $newStatus == 1 ? "API key đã được kích hoạt!" : "API key đã được vô hiệu hóa!";
    } else {
        $_SESSION['message'] = "API key không tồn tại.";
    }

    header("Location: " . $_SERVER['PHP_SELF']); // Refresh lại trang
    exit;
}
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
