    <!-- Liên kết phân trang -->
    <nav>
    <ul class="pagination">
        <!-- Nút đến trang đầu tiên -->
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="view.php?id=<?php echo htmlspecialchars($postId, ENT_QUOTES, 'UTF-8'); ?>&page=1">&laquo; Đầu</a>
        </li>
        <?php endif; ?>

        <?php 
        // Hiển thị trang đầu tiên nếu không phải trang đầu tiên
        if ($page > 4) echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=1">1</a></li>';
        
        // Hiển thị dấu "..." nếu có trang ở giữa
        if ($page > 4) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
        
        // Các trang trước trang hiện tại (hiển thị 3 trang trước đó)
        for ($i = max(1, $page - 3); $i < $page; $i++): ?>
        <li class="page-item">
            <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>

        <!-- Trang hiện tại -->
        <li class="page-item active">
            <a class="page-link" href="#"><?php echo $page; ?></a>
        </li>

        <!-- Các trang sau trang hiện tại (hiển thị 3 trang tiếp theo) -->
        <?php for ($i = $page + 1; $i <= min($totalPages, $page + 3); $i++): ?>
        <li class="page-item">
            <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
        <?php endfor; ?>

        <?php 
        // Hiển thị dấu "..." nếu có trang ở giữa
        if ($page < $totalPages - 3) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';

        // Hiển thị trang cuối cùng nếu không phải trang cuối cùng
        if ($page < $totalPages - 3) echo '<li class="page-item"><a class="page-link" href="view.php?id=' . urlencode($postId) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
        ?>

        <!-- Nút đến trang cuối cùng -->
        <?php if ($page < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="view.php?id=<?php echo urlencode($postId); ?>&page=<?php echo $totalPages; ?>">Cuối &raquo;</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>