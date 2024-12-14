<?php
include('../config.php');
include('../app/profile/Handle.php');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ của <?php echo htmlspecialchars($user_info['username']); ?></title>
    <!-- Thêm Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJQ3U8WJbL3aUcxISn0UOXU+d1ebJXKm1A7P+zL5gt6pgB5yOeFCcmAfA4h7" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="../app/profile/styles.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hồ sơ của <?php echo htmlspecialchars($user_info['username']); ?></h1>
        </div>

        <div class="profile-info">
            <h2>Thông tin người dùng</h2>
            <p><strong>Tên người dùng:</strong> <?php echo htmlspecialchars($user_info['username']); ?></p>
            <p><strong>Mô tả:</strong> <?php echo nl2br(htmlspecialchars($user_info['desc'])); ?></p>
        </div>

        <?php if ($result_posts->num_rows > 0): ?>
        <div class="posts">
            <h2>Bài viết của <?php echo htmlspecialchars($user_info['username']); ?></h2>
            <?php while ($post = $result_posts->fetch_assoc()): ?>
            <div class="post-item">
                <h3><?php echo htmlspecialchars($post['content']); ?></h3>
                <?php if (!empty($post['description'])): ?>
                <p class="description"><em><?php echo nl2br(htmlspecialchars($post['description'])); ?></em></p>
                <?php endif; ?>
                <p><small>Đăng vào: <?php echo $post['created_at']; ?></small></p>
                <small><a href="src/view.php?id=<?php echo $post['id']; ?>" class="read-more">Xem thêm</a></small>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <p>Chưa có bài viết nào.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
// Đóng kết nối
$stmt_user->close();
$stmt_posts->close();
$conn->close();
?>
