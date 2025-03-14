<?php
session_start();
include '../config.php'; 
include '../app/_ADMIN_TOOLS/api/php.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý API</title>
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/api/styles.css">
    <link rel="stylesheet" type="text/css" href="/app/_ADMIN_TOOLS/api/Pagination.css">
  
</head>
<body>

    <h1>Quản lý API Keys</h1>
<!-- Nút quay về trang admin và nút tạo API Key -->
<div style="margin-top: 20px; display: flex; align-items: center; gap: 10px;">
    <a href="/admin_tool/admin.php" style="padding: 8px 15px; background-color: #007bff; color: #fff; text-decoration: none; border-radius: 5px;">
        Về trang admin
    </a>
    <form method="POST">
        <button type="submit" name="generate_key" class="btn btn-generate">Tạo API Key mới</button>
    </form>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <p class="message" style="margin-top: 10px;"><?= $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php endif; ?>


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

    <!-- Phân trang -->
    <div class="pagination">
        <a href="?page=1" class="page-link <?= $page == 1 ? 'disabled' : ''; ?>"><<</a>
        <a href="?page=<?= $page - 1; ?>" class="page-link <?= $page == 1 ? 'disabled' : ''; ?>">‹</a>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i > $page - 5 && $i < $page + 5): ?>
                <a href="?page=<?= $i; ?>" class="page-link <?= $i == $page ? 'active' : ''; ?>"><?= $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <a href="?page=<?= $page + 1; ?>" class="page-link <?= $page == $totalPages ? 'disabled' : ''; ?>">›</a>
        <a href="?page=<?= $totalPages; ?>" class="page-link <?= $page == $totalPages ? 'disabled' : ''; ?>">>></a>
    </div>
</body>
</html>
