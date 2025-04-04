<?php
function renderComments($conn, $post_id) {
    $pagination = getCommentsPagination($conn, $post_id);
    $result_comments = $pagination['result_comments'];
    $cpage_param = $pagination['cpage_param'];
    $cpage = $pagination['cpage'];
    $total_comment_pages = $pagination['total_comment_pages'];

    ob_start();
    if (mysqli_num_rows($result_comments) > 0) {
        while ($comment = mysqli_fetch_assoc($result_comments)) {
            ?>
            <div class="border p-2 mb-2">
                <p>
                    <strong><?= htmlspecialchars($comment['username']) ?></strong> 
                    - <small><?= htmlspecialchars($comment['created_at']) ?></small>
                </p>
                <p><?= htmlspecialchars($comment['content']) ?></p>
                <div class="dropdown mb-2">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownComment<?= $comment['id'] ?>" data-bs-toggle="dropdown" aria-expanded="false">
                        Hành động
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownComment<?= $comment['id'] ?>">
                        <?php if ($_SESSION['role'] === 'owner' || ($_SESSION['role'] === 'admin' && $comment['user_role'] === 'member')): ?>
                            <li>
                                <form method="post" action="" style="display:inline;">
                                    <input type="hidden" name="delete_comment" value="<?= $comment['id'] ?>">
                                    <button type="submit" class="dropdown-item" onclick="return confirm('Bạn có chắc chắn muốn xóa bình luận này không?');">
                                        Xóa
                                    </button>
                                </form>
                            </li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item" data-bs-toggle="collapse" href="#collapseReplies<?= $comment['id'] ?>" role="button" aria-expanded="false" aria-controls="collapseReplies<?= $comment['id'] ?>">
                                Xem phản hồi
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="collapse" id="collapseReplies<?= $comment['id'] ?>">
                    <div class="card card-body">
                        <?= renderReplies($conn, $comment['id']) ?>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        echo '<p class="text-muted">Chưa có bình luận.</p>';
    }
    renderPagination($cpage_param, $cpage, $total_comment_pages);
    return ob_get_clean();
}
?>
