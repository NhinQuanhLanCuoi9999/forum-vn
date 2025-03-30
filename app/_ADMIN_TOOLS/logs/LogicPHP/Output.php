<?php
// Các file log dành cho admin
$adminLogs = ['admin/admin-log.txt', 'admin/api.txt', 'admin/backup.txt', 'admin/ban-logs.txt'];

// Kiểm tra file có nằm trong danh sách admin không
$logDir = in_array($selectedLog, $adminLogs) ? '/logs/admin/' : '/logs/users/';

// Đường dẫn chính xác đến file log
$filePath = $_SERVER['DOCUMENT_ROOT'] . $logDir . basename($selectedLog);

// Số dòng mỗi trang
$linesPerPage = 15;

// Kiểm tra xem file có tồn tại và có quyền đọc không
if (file_exists($filePath) && is_readable($filePath)) {
    // Đọc nội dung file
    $fileContent = file_get_contents($filePath);

    // Chia nội dung thành từng dòng (hỗ trợ đa nền tảng)
    $logLines = preg_split('/\R/', $fileContent);

    // Loại bỏ dòng trống
    $logLines = array_filter($logLines, 'trim');

    // Đảo ngược các dòng để log mới nhất ở đầu
    $logLines = array_reverse($logLines);

    // Tổng số dòng log
    $totalLines = count($logLines);

    // Tính toán số trang
    $totalPages = ceil($totalLines / $linesPerPage);

    // Lấy trang hiện tại từ query string (mặc định là trang 1)
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

    // Đảm bảo trang hợp lệ
    if ($currentPage < 1) {
        $currentPage = 1;
    } elseif ($currentPage > $totalPages) {
        $currentPage = $totalPages;
    }

    // Tính toán vị trí bắt đầu của trang
    $start = ($currentPage - 1) * $linesPerPage;

    // Lấy các dòng cần hiển thị cho trang hiện tại
    $logLinesToDisplay = array_slice($logLines, $start, $linesPerPage);

    // Hiển thị các dòng log cho trang hiện tại
    echo '<pre>' . htmlspecialchars(implode("\n", $logLinesToDisplay)) . '</pre>';

    // Hiển thị liên kết phân trang theo template mới
    echo '<div style="margin-top: 20px;">';

    // Xây dựng URL cơ bản cho phân trang
    $baseUrl = strtok($_SERVER["REQUEST_URI"], '?');
    $queryString = $_SERVER["QUERY_STRING"];
    parse_str($queryString, $queryParams);
    unset($queryParams['page']);
    $baseQueryString = http_build_query($queryParams);
    $pageUrl = $baseUrl . '?' . $baseQueryString . ($baseQueryString ? '&' : '');

    // Liên kết đến trang đầu tiên
    if ($currentPage > 1) {
        echo "<a href='" . $pageUrl . "page=1'>&lt;&lt;</a> ";
    }

    // Liên kết đến trang trước
    if ($currentPage > 1) {
        echo "<a href='" . $pageUrl . "page=" . ($currentPage - 1) . "'>&lt;</a> ";
    }

    // Hiển thị các liên kết trang gần với trang hiện tại
    $range = 7; // Số trang hiển thị xung quanh trang hiện tại
    for ($i = max(1, $currentPage - $range); $i <= min($totalPages, $currentPage + $range); $i++) {
        if ($i == $currentPage) {
            echo "<strong>$i</strong> "; // Đánh dấu trang hiện tại
        } else {
            echo "<a href='" . $pageUrl . "page=$i'>$i</a> ";
        }
    }

    // Liên kết đến trang tiếp theo
    if ($currentPage < $totalPages) {
        echo "<a href='" . $pageUrl . "page=" . ($currentPage + 1) . "'>&gt;</a> ";
    }

    // Liên kết đến trang cuối cùng
    if ($currentPage < $totalPages) {
        echo "<a href='" . $pageUrl . "page=$totalPages'>&gt;&gt;</a>";
    }

    echo "</div>";
} else {
    echo '<span class="error">Không thể truy cập tệp log.</span>';
}
?>
