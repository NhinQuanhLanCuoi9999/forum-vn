<?php
function render_pagination($currentPage, $totalPages, $searchTerm = '') {
    $renderForm = function($pageNum, $label, $isActive = false, $searchTerm) {
        $btnClass = $isActive
            ? 'btn-primary'
            : ($label === '<<<' || $label === '>>>' ? 'btn-secondary' : 'btn-outline-secondary');
        ?>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="d-inline-block me-2">
            <?php if ($searchTerm !== ''): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchTerm); ?>">
                <input type="hidden" name="search_submit" value="1">
            <?php endif; ?>
            <input type="hidden" name="page" value="<?= $pageNum; ?>">
            <button type="submit" class="btn <?= $btnClass; ?>"><?= $label; ?></button>
        </form>
        <?php
    };

    echo '<div class="d-flex justify-content-center">';
    
    // nút <<<
    if ($currentPage > 1) {
        $renderForm(1, '<<<', false, $searchTerm);
    }

    // các trang xung quanh
    $start = max(1, $currentPage - 2);
    $end   = min($totalPages, $currentPage + 2);
    for ($i = $start; $i <= $end; $i++) {
        $renderForm($i, $i, $i === $currentPage, $searchTerm);
    }

    // nút >>>
    if ($currentPage < $totalPages) {
        $renderForm($totalPages, '>>>', false, $searchTerm);
    }

    echo '</div>';
}
?>