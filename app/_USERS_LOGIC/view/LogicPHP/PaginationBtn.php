<?php
function renderPagination($postId, $page, $totalPages) {
    if ($totalPages <= 1) return;

    echo '<nav><ul class="pagination">';

    if ($page > 1) {
        echo '<li class="page-item"><a class="page-link" href="view.php?id=' . htmlspecialchars($postId, ENT_QUOTES, 'UTF-8') . '&page=1">&laquo; Đầu</a></li>';
    }

    if ($page > 4) {
        echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=1">1</a></li>';
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }

    for ($i = max(1, $page - 3); $i < $page; $i++) {
        echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=' . $i . '">' . $i . '</a></li>';
    }

    echo '<li class="page-item active"><a class="page-link" href="#">' . $page . '</a></li>';

    for ($i = $page + 1; $i <= min($totalPages, $page + 3); $i++) {
        echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=' . $i . '">' . $i . '</a></li>';
    }

    if ($page < $totalPages - 3) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
    }

    if ($page < $totalPages) {
        echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=' . $totalPages . '">Cuối &raquo;</a></li>';
    }

    echo '</ul></nav>';
}

?>