<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once('../config.php');
include '../app/_ADMIN_TOOLS/post/php.php';


function renderReplies($conn, $comment_id) {
    $replyPagination = getRepliesPagination($conn, $comment_id);
    $result_replies = $replyPagination['result_replies'];
    $rpage_param = $replyPagination['rpage_param'];
    $rpage = $replyPagination['rpage'];
    $total_reply_pages = $replyPagination['total_reply_pages'];

    ob_start();
    ?>
    <div class="collapse mt-2" id="collapseReplies<?= $comment_id ?>">
        <div class="card card-body">
            <?php
            if (mysqli_num_rows($result_replies) > 0) {
                while ($reply = mysqli_fetch_assoc($result_replies)) {
                    ?>
                    <div class="border p-2 mb-2">
                        <p>
                            <strong><?= htmlspecialchars($reply['username']) ?></strong> 
                            - <small><?= htmlspecialchars($reply['created_at']) ?></small>
                            <?php if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && $reply['user_role'] === 'member')): ?>
                                <a href="?delete_reply=<?= $reply['id'] ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Bạn có chắc chắn muốn xóa phản hồi này không?');">
                                   Xóa
                                </a>
                            <?php endif; ?>
                        </p>
                        <p><?= htmlspecialchars($reply['content']) ?></p>
                    </div>
                    <?php
                }
            } else {echo '<p class="text-muted">Chưa có phản hồi.</p>';}
            renderPagination($rpage_param, $rpage, $total_reply_pages);
            ?>
        </div>
    </div>
    <?php return ob_get_clean();}

function renderPosts($conn, $result) {
    ob_start();
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong>ID: <?= $row['id'] ?></strong>
                </div>
                <div class="card-body">
                    <p><strong>Nội dung:</strong> <?= htmlspecialchars($row['content'] ?? '') ?></p>
                    <p><strong>Mô tả:</strong> <?= htmlspecialchars($row['description'] ?? '') ?></p>
                    <p><strong>Tác giả:</strong> <?= htmlspecialchars($row['username'] ?? '') ?></p>
                    <p><small class="text-muted"><strong>Thời gian:</strong> <?= htmlspecialchars($row['created_at'] ?? '') ?></small></p>
                    <?= renderPostActions($row) ?>
                    <div class="collapse" id="collapseComments<?= $row['id'] ?>">
                        <div class="card card-body">
                            <?= renderComments($conn, $row['id']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<div class="alert alert-info">Không có bài đăng nào được tìm thấy!</div>';
    }
    return ob_get_clean();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <?php displayAlert(); ?>
    <title>Quản lý bài đăng</title>
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Quản lý bài đăng</h1>
    <div class="mb-3">
        <a href="/admin_tool/admin.php" class="btn btn-warning">Về Trang Admin</a>
    </div>
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Tìm bài viết..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>
    <?= renderPosts($conn, $result) ?>
    <?php renderPagination('page', $page, $total_pages); ?>
</div>
<script src="/asset/js/Bootstrap.bundle.min.js"></script>
<script src="/asset/js/jquery.min.js"></script>
<script src="/app/_ADMIN_TOOLS/post/LocalStorage.js"></script>
</body>
</html>
