<?php
session_start();
include('../config.php');
include('../app/ban/php.php');

// Kiểm tra xem có dữ liệu 'username' không, nếu không thì gán là NULL
$username = isset($_POST['username']) && !empty($_POST['username']) ? $_POST['username'] : null;

// Xác định số bản ghi mỗi trang và trang hiện tại
$perPage = 10; // Số lượng lệnh cấm mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Lấy trang hiện tại từ URL, mặc định là 1

// Tính toán tổng số lệnh cấm
$sql = "SELECT COUNT(*) AS total FROM bans";
$result = $conn->query($sql);
$totalBans = $result->fetch_assoc()['total'];

// Tính tổng số trang
$totalPages = ceil($totalBans / $perPage);

// Giới hạn kết quả cho trang hiện tại
$offset = ($page - 1) * $perPage;
$sql = "SELECT * FROM bans ORDER BY ban_end DESC LIMIT $offset, $perPage";
$bans = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.png" type="image/png">
    <title>Quản lý lệnh cấm</title>
    <link rel="stylesheet" type="text/css" href="/app/ban/style.css">
</head>
<body>
    <button class="redirect-button" onclick="window.location.href='src/admin.php'">Quay lại</button>
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

<!-- Phân trang với CSS inline -->
<nav aria-label="Page navigation">
    <ul class="pagination" style="display: flex; list-style: none; padding: 0; margin: 0;">
        <?php if ($page > 1): ?>
            <li class="page-item" style="margin-right: 5px;">
                <a class="page-link" href="?page=1" aria-label="First" style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; background-color: #f8f9fa; color: #007bff;">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <li class="page-item" style="margin-right: 5px;">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous" style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; background-color: #f8f9fa; color: #007bff;">
                    <span aria-hidden="true">&lsaquo;</span>
                </a>
            </li>
        <?php endif; ?>

        <?php
        // Hiển thị các trang
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);

        for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item" style="margin-right: 5px;">
                <a class="page-link" href="?page=<?php echo $i; ?>" style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; background-color: <?php echo ($i == $page) ? '#007bff' : '#f8f9fa'; ?>; color: <?php echo ($i == $page) ? 'white' : '#007bff'; ?>;">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item" style="margin-right: 5px;">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next" style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; background-color: #f8f9fa; color: #007bff;">
                    <span aria-hidden="true">&rsaquo;</span>
                </a>
            </li>
            <li class="page-item" style="margin-right: 5px;">
                <a class="page-link" href="?page=<?php echo $totalPages; ?>" aria-label="Last" style="padding: 10px 15px; border: 1px solid #ccc; border-radius: 5px; text-decoration: none; background-color: #f8f9fa; color: #007bff;">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>



</div>
<script src="../app/ban/banOption.js"></script>
</body>
</html>
