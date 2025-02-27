<?php
session_start();
include '../config.php';
include '../app/authencation/Handle.php';
include '../app/admin/logicPHP/Auth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Admin</title>
    <link rel="stylesheet" href="/asset/css/Bootstrap2.min.css">
    <style>
        <?php if ($notAdmin): ?>.alert-permission {position: fixed;top: 0;left: 0;width: 100%;height: 100%;background: rgba(0, 0, 0, 0.7);display: flex;justify-content: center;align-items: center;z-index: 9999;}
        .alert-box {background: white;padding: 20px;border-radius: 10px;text-align: center;box-shadow: 0px 0px 15px rgba(255, 255, 255, 0.3);}
        <?php else: ?>
        .alert-permission {display: none;}
        <?php endif; ?>
    </style>
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Quản Lý Admin</h1>
    <!-- Nút về trang admin.php -->
    <a href="/admin_tool/admin.php" class="btn btn-info mb-3">Về Trang Admin</a>
    
    <p>Xin chào, <strong><?php echo htmlspecialchars($currentUser); ?></strong></p>

    <?php if ($msg != ""): ?>
        <div class="alert alert-info"><?php echo $msg; ?></div>
    <?php endif; ?>
    
    <?php if (!$notAdmin): ?>
    <form method="post" class="form-inline mb-4">
        <div class="form-group mr-3">
            <label for="name" class="sr-only">Tên người dùng</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên user" required>
        </div>
        <input type="hidden" name="action" value="add">
        <button type="submit" name="submit" value="add" class="btn btn-primary">Thêm Admin</button>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Tên User</th>
                <th>Vai Trò</th>
                <th>Hành Động</th>
            </tr>
        </thead>
        <tbody>
        <?php
$stmt = $conn->prepare("SELECT username, role FROM users WHERE role = ?");
$role = 'admin';
$stmt->bind_param("s", $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()):
        $isCurrentUser = ($row['username'] === $currentUser);
?>

                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                            <?php if (!$isCurrentUser): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="name" value="<?php echo htmlspecialchars($row['username']); ?>">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(this)">Xóa Admin</button>
                                    <button type="submit" name="submit" value="confirm_remove" class="btn btn-warning btn-sm d-none">Chắc chắn xóa</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-sm" disabled>Không thể xóa chính bạn</button>
                            <?php endif; ?>
                        </td>
                    </tr>
        <?php
    endwhile;
} else {
    echo "<tr><td colspan='3'>Không có Admin nào</td></tr>";
}
$conn->close();
        ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php if ($notAdmin): ?>
    <div class="alert-permission">
        <div class="alert-box">
            <h2>Không đủ quyền truy cập</h2>
            <p>Bạn cần quyền Owner để truy cập trang này.</p>
            <a href="/index.php" class="btn btn-danger">Quay lại Trang Chủ</a>
        </div>
    </div>
<?php endif; ?>

<script>
function confirmDelete(btn) {
    let confirmBtn = btn.nextElementSibling;
    if (confirmBtn) {
        confirmBtn.classList.remove("d-none");
        btn.classList.add("d-none");
    }
}
</script>
</body>
</html>
