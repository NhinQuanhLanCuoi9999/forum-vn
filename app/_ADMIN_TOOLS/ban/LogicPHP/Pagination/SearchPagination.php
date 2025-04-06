<?php
include($_SERVER['DOCUMENT_ROOT'] . '/app/_ADMIN_TOOLS/ban/LogicPHP/Search/Render.php');
$limit = 10;
// Hàm render phân trang, nếu $ajax = true thì sinh ra các link cho Ajax (search)
function renderPagination($currentPage, $totalPages, $ajax = false) {
    if ($totalPages <= 1) return;
    
    if ($ajax) {
        echo '<nav class="mt-3" aria-label="Search page navigation">';
        echo '<ul class="pagination justify-content-center">';
        if ($currentPage > 1) {
            echo '<li class="page-item"><a class="page-link search-page" href="#" data-page="1">«</a></li>';
            echo '<li class="page-item"><a class="page-link search-page" href="#" data-page="' . ($currentPage - 1) . '">‹</a></li>';
        }
        for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
            $active = ($currentPage == $i) ? ' active' : '';
            echo '<li class="page-item' . $active . '"><a class="page-link search-page" href="#" data-page="' . $i . '">' . $i . '</a></li>';
        }
        if ($currentPage < $totalPages) {
            echo '<li class="page-item"><a class="page-link search-page" href="#" data-page="' . ($currentPage + 1) . '">›</a></li>';
            echo '<li class="page-item"><a class="page-link search-page" href="#" data-page="' . $totalPages . '">»</a></li>';
        }
        echo '</ul>';
        echo '</nav>';
    } else {
        echo '<nav class="mt-3" aria-label="Page navigation">';
        echo '<ul class="pagination justify-content-center">';
        if ($currentPage > 1) {
            echo '<li class="page-item"><a class="page-link" href="?page=1">&laquo;</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage - 1) . '">&lsaquo;</a></li>';
        }
        for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++) {
            $active = ($currentPage == $i) ? ' active' : '';
            echo '<li class="page-item' . $active . '">';
            echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
            echo '</li>';
        }
        if ($currentPage < $totalPages) {
            echo '<li class="page-item"><a class="page-link" href="?page=' . ($currentPage + 1) . '">&rsaquo;</a></li>';
            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">&raquo;</a></li>';
        }
        echo '</ul>';
        echo '</nav>';
    }
}


// Xử lý Ajax tìm kiếm có hỗ trợ phân trang
if (isset($_GET['action']) && $_GET['action'] === 'search') {
    $q = isset($_GET['q']) ? $_GET['q'] : '';
    $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
    if ($currentPage < 1) $currentPage = 1;
    
    $searchValue = "%" . $conn->real_escape_string($q) . "%";

    // Lấy tổng số bản ghi phù hợp
    $countSql = "SELECT COUNT(*) as total FROM bans WHERE username LIKE ? OR ip_address LIKE ? OR reason LIKE ?";
    if ($stmtCount = $conn->prepare($countSql)) {
        $stmtCount->bind_param("sss", $searchValue, $searchValue, $searchValue);
        $stmtCount->execute();
        $countResult = $stmtCount->get_result()->fetch_assoc();
        $totalRecords = (int)$countResult['total'];
        $totalPages = ceil($totalRecords / $limit);
        $stmtCount->close();
    } else {
        echo '<p class="text-danger">Lỗi truy vấn (count).</p>';
        exit;
    }
    
    $offset = ($currentPage - 1) * $limit;
    $sql = "SELECT * FROM bans WHERE username LIKE ? OR ip_address LIKE ? OR reason LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssii", $searchValue, $searchValue, $searchValue, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        ob_start();
        echo '<div class="list-group">';
        renderBanList($result);
        echo '</div>';
        // Gọi hàm renderPagination cho kết quả tìm kiếm
        renderPagination($currentPage, $totalPages, true);
        $html = ob_get_clean();
        echo $html;
        exit;
    } else {
        echo '<p class="text-danger">Lỗi truy vấn.</p>';
        exit;
    }
}

// Xử lý truy vấn và phân trang cho trang chính
$currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($currentPage < 1) $currentPage = 1;

// Lấy tổng số bản ghi cho trang chính
$countSql = "SELECT COUNT(*) as total FROM bans";
$countResult = $conn->query($countSql)->fetch_assoc();
$totalRecords = (int)$countResult['total'];
$totalPages = ceil($totalRecords / $limit);
$offset = ($currentPage - 1) * $limit;

// Lấy danh sách ban cho trang chính
$sql = "SELECT * FROM bans ORDER BY id DESC LIMIT ? OFFSET ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $bans = $stmt->get_result();
    $stmt->close();
} else {
    $bans = [];
}
?>