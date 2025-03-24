<?php
// Hàm build_url: chỉ thêm tham số nếu có giá trị
function build_url($page, $search, $start_date, $end_date) {
    $params = ['page' => $page]; // luôn có page
    if (!empty($search)) {
        $params['search'] = $search;
    }
    if (!empty($start_date)) {
        $params['start_date'] = $start_date;
    }
    if (!empty($end_date)) {
        $params['end_date'] = $end_date;
    }
    return "?" . http_build_query($params);
}

if ($result->num_rows > 0) {
    if ($total_pages > 1) {
        echo "<div class='pagination'>";
        
        // Hiển thị <<< nếu trang hiện tại > 3
        if ($page > 3) {
            echo "<a href='" . build_url(1, $search, $start_date, $end_date) . "'>&lt;&lt;&lt;</a> ";
        }
        
        // Luôn hiển thị trang 1
        echo "<a href='" . build_url(1, $search, $start_date, $end_date) . "'" . ($page == 1 ? " class='active'" : "") . ">1</a> ";
        
        // Xác định nhóm trang giữa
        $start_window = max(2, $page - 2);
        if ($start_window > 2) {
            if (($start_window - 2) >= 5) {
                echo "<span>...</span> ";
            } else {
                for ($i = 2; $i < $start_window; $i++) {
                    echo "<a href='" . build_url($i, $search, $start_date, $end_date) . "'>$i</a> ";
                }
            }
        }
        
        $end_window = min($total_pages - 1, $page + 2);
        for ($i = $start_window; $i <= $end_window; $i++) {
            echo "<a href='" . build_url($i, $search, $start_date, $end_date) . "'" . ($page == $i ? " class='active'" : "") . ">$i</a> ";
        }
        
        if ($total_pages - $end_window > 1) {
            if (($total_pages - $end_window - 1) >= 5) {
                echo "<span>...</span> ";
            } else {
                for ($i = $end_window + 1; $i < $total_pages; $i++) {
                    echo "<a href='" . build_url($i, $search, $start_date, $end_date) . "'>$i</a> ";
                }
            }
        }
        
        // Hiển thị trang cuối
        if ($total_pages > 1) {
            echo "<a href='" . build_url($total_pages, $search, $start_date, $end_date) . "'" . ($page == $total_pages ? " class='active'" : "") . ">$total_pages</a> ";
        }
        
        // Hiển thị >>> nếu trang hiện tại < (tổng trang - 2)
        if ($page < $total_pages - 2) {
            echo "<a href='" . build_url($total_pages, $search, $start_date, $end_date) . "'>&gt;&gt;&gt;</a> ";
        }
        
        echo "</div>";
    }
}
?>
