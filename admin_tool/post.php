<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
require_once('../config.php');
include '../app/_ADMIN_TOOLS/admin/logicPHP/Auth.php';
include '../app/_ADMIN_TOOLS/post/PostHandle.php';
include '../app/_ADMIN_TOOLS/post/CommentsHandle.php';
include '../app/_ADMIN_TOOLS/post/Pagination/PaginationBtn.php';
include '../app/_ADMIN_TOOLS/post/ReplyHandle.php';
include '../app/_ADMIN_TOOLS/post/Pagination/Pagination.php';
include '../app/_ADMIN_TOOLS/post/Search.php';
if (isset($_SESSION['alert'])) {echo $_SESSION['alert'];unset($_SESSION['alert']);}

function writeLog($id, $content, $type) {
    $log_dir = $_SERVER['DOCUMENT_ROOT'] . "/logs/";
    $log_file = $log_dir . "admin-log.txt";

    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    $user_name = $_SESSION['username'] ?? 'Unknown';
    $user_ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    $log_entry = "[" . date("d/m/Y | H:i:s") . "] Người dùng : [$user_name] (IP: $user_ip) đã thao tác xóa $type có ID [$id] ( nội dung : [$content])\n";

    @file_put_contents($log_file, $log_entry, FILE_APPEND);
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý bài đăng</title>
    <link href="/asset/css/Bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Quản lý bài đăng</h1>
     <!-- Nút về trang admin -->
     <div class="mb-3">
        <a href="/admin_tool/admin.php" class="btn btn-warning">Về Trang Admin</a>
    </div>
    <!-- Form tìm kiếm siêu đẹp-->
    <form method="get" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" name="search" placeholder="Tìm bài viết..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        </div>
    </form>
    
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <strong>ID: <?= $row['id'] ?></strong>
            </div>
            <div class="card-body">
                <p><strong>Nội dung:</strong> <?= htmlspecialchars($row['content'] ?? '') ?></p>
                <p><strong>Mô tả:</strong> <?= htmlspecialchars($row['description'] ?? '') ?></p>
                <p><strong>Tác giả:</strong> <?= htmlspecialchars($row['username'] ?? '') ?></p>
                <p><small class="text-muted"><strong>Thời gian:</strong> <?= htmlspecialchars($row['created_at'] ?? '') ?></small></p>
                <div class="mb-2">
                    <?php if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && ($row['role'] ?? 'member') === 'member')): ?>
                        <a href="?delete=<?= $row['id'] ?>" 
                           class="btn btn-danger btn-sm" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa bài đăng này không?');">
                           Xóa
                        </a>
                    <?php endif; ?>
                    <a class="btn btn-primary btn-sm" data-bs-toggle="collapse" href="#collapseComments<?= $row['id'] ?>" role="button" aria-expanded="false" aria-controls="collapseComments<?= $row['id'] ?>">
                        Xem Bình Luận
                    </a>
                </div>
                <!-- PHÂN TRANG CHO BÌNH LUẬN (theo mỗi bài đăng) -->
                <div class="collapse" id="collapseComments<?= $row['id'] ?>">
                    <div class="card card-body">
                        <?php 
                        $post_id = $row['id'];
                        $comments_per_page = 7;
                        $cpage_param = "cpage_" . $post_id;
                        $cpage = isset($_GET[$cpage_param]) ? (int)$_GET[$cpage_param] : 1;
                        if ($cpage < 1) $cpage = 1;
                        // Đếm tổng số bình luận cho bài đăng này
                        $total_comments_query = "SELECT COUNT(*) as total FROM comments WHERE post_id = $post_id";
                        $total_comments_result = mysqli_query($conn, $total_comments_query);
                        $total_comments_row = mysqli_fetch_assoc($total_comments_result);
                        $total_comments = $total_comments_row['total'];
                        $total_comment_pages = ceil($total_comments / $comments_per_page);
                        $comment_offset = ($cpage - 1) * $comments_per_page;
                        
                        $query_comments = "SELECT comments.*, users.role AS user_role 
                                           FROM comments 
                                           JOIN users ON comments.username = users.username 
                                           WHERE post_id = $post_id 
                                           ORDER BY created_at DESC
                                           LIMIT $comment_offset, $comments_per_page";
                        $result_comments = mysqli_query($conn, $query_comments);
                        if(mysqli_num_rows($result_comments) > 0):
                            while($comment = mysqli_fetch_assoc($result_comments)):
                        ?>
                            <div class="border p-2 mb-2">
                                <p>
                                    <strong><?= htmlspecialchars($comment['username']) ?></strong> 
                                    - <small><?= htmlspecialchars($comment['created_at']) ?></small>
                                    <?php if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && $comment['user_role'] === 'member')): ?>
                                        <a href="?delete_comment=<?= $comment['id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này không?');">
                                           Xóa
                                        </a>
                                    <?php endif; ?>
                                </p>
                                <p><?= htmlspecialchars($comment['content']) ?></p>
                                <!-- PHÂN TRANG CHO PHẢN HỒI (theo mỗi bình luận) -->
                                <a class="btn btn-secondary btn-sm" data-bs-toggle="collapse" href="#collapseReplies<?= $comment['id'] ?>" role="button" aria-expanded="false" aria-controls="collapseReplies<?= $comment['id'] ?>">
                                    Xem Phản Hồi
                                </a>
                                <div class="collapse mt-2" id="collapseReplies<?= $comment['id'] ?>">
                                    <div class="card card-body">
                                    <?php 
                                        $comment_id = intval($comment['id']);
                                        $replies_per_page = 7;
                                        $rpage_param = "rpage_" . $comment_id;
                                        $rpage = isset($_GET[$rpage_param]) ? (int)$_GET[$rpage_param] : 1;
                                        if ($rpage < 1) $rpage = 1;

                                        // Đếm tổng số phản hồi cho bình luận này (DÙNG PREPARED STATEMENT)
                                        $total_replies_query = "SELECT COUNT(*) as total FROM replies WHERE comment_id = ?";
                                        $stmt = mysqli_prepare($conn, $total_replies_query);
                                        mysqli_stmt_bind_param($stmt, "i", $comment_id);
                                        mysqli_stmt_execute($stmt);
                                        $result_reply_count = mysqli_stmt_get_result($stmt);
                                        $total_replies_row = mysqli_fetch_assoc($result_reply_count);
                                        $total_replies = $total_replies_row['total'];
                                        $total_reply_pages = ceil($total_replies / $replies_per_page);
                                        mysqli_stmt_close($stmt);

                                        $reply_offset = ($rpage - 1) * $replies_per_page;

                                        // Truy vấn phản hồi (DÙNG PREPARED STATEMENT)
                                        $query_replies = "SELECT replies.*, users.role AS user_role FROM replies JOIN users ON replies.username = users.username WHERE comment_id = ? ORDER BY created_at DESC LIMIT ?, ?";
                                        $stmt = mysqli_prepare($conn, $query_replies);
                                        mysqli_stmt_bind_param($stmt, "iii", $comment_id, $reply_offset, $replies_per_page);
                                        mysqli_stmt_execute($stmt);
                                        $result_replies = mysqli_stmt_get_result($stmt);

                                        if (mysqli_num_rows($result_replies) > 0):
                                            while ($reply = mysqli_fetch_assoc($result_replies)):
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
                                            endwhile;
                                        else:
                                    ?>
                                            <p class="text-muted">Chưa có phản hồi.</p>
                                    <?php endif; ?>
                                    <!-- Render phân trang cho phản hồi -->
                                    <?php renderPagination($rpage_param, $rpage, $total_reply_pages); ?>
                                    </div>
                                </div>
                            </div>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <p class="text-muted">Chưa có bình luận.</p>
                        <?php endif; ?>
                        <!-- Render phân trang cho bình luận -->
                        <?php renderPagination($cpage_param, $cpage, $total_comment_pages); ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    <!-- Render phân trang cho bài đăng -->
    <?php renderPagination('page', $page, $total_pages); ?>
</div>
<script src="/asset/js/Bootstrap.bundle.min.js"></script>
<script src="/asset/js/jquery.min.js"></script>
<script src="/app/_ADMIN_TOOLS/post/LocalStorage.js"></script>
</body>
</html>
