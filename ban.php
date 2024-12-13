<?php
session_start();
include('config.php');
include('ban\php.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Quản lý lệnh cấm</title>
    <link rel="stylesheet" type="text/css" href="ban/style.css">
</head>
<body>
    <button class="redirect-button" onclick="window.location.href='admin.php'">Quay lại</button>
    <div class="container">
        <h1>Quản lý lệnh cấm</h1>

        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>

        <form method="POST" onsubmit="return confirmBan();">
            <input type="text" name="username" placeholder="Tên người dùng (có thể để trống)">
            <input type="text" name="ip_address" placeholder="IP muốn cấm">
            <textarea name="reason" placeholder="Lý do cấm" required></textarea>
            <input type="datetime-local" name="ban_end" required>
            <small style="color: #6c757d;">Chọn ngày và giờ kết thúc lệnh cấm</small>
            <input type="submit" name="ban" value="Cấm">
        </form>

        <h2>Danh sách người dùng bị cấm</h2>
        <div class="ban-list">
            <?php if ($bans->num_rows === 0): ?>
                <p class="info-message">Chưa có người dùng nào bị cấm.</p>
            <?php else: ?>
                <?php while ($ban = $bans->fetch_assoc()): ?>
                    <div class="ban-item">
                        <p>Người dùng: <?php echo htmlspecialchars($ban['username'] ?? 'Không xác định'); ?> - 
                        IP: <?php echo htmlspecialchars($ban['ip_address']); ?> - 
                        Lý do: <?php echo htmlspecialchars($ban['reason']); ?> - 
                        Đến: <?php echo htmlspecialchars($ban['ban_end']); ?> 
                        <a class="unban-link" href="ban.php?unban=<?php echo $ban['id']; ?>" onclick="return confirmUnban();">Hủy cấm</a></p>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>
<script src = ban/banOption.js></script>
</body>
</html>
