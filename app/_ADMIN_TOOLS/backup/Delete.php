<?php
include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

$backupFolder = $_SERVER['DOCUMENT_ROOT'] . '/backup/';
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/backup.txt';

// Kiểm tra quyền hạn: Chỉ owner mới được phép xóa file
if (isset($_GET['delete'])) { 
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
        die("<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
              <strong>Bạn không có quyền thực hiện thao tác này.</strong>
              </div>");
    }
}


// Nếu có tham số delete trong URL
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']); // Chỉ lấy tên file, tránh path traversal
    $filePath = $backupFolder . $fileToDelete;

    // Kiểm tra file tồn tại & có đúng đuôi .sql không
    if (file_exists($filePath) && pathinfo($filePath, PATHINFO_EXTENSION) === 'sql') {
        unlink($filePath);
        writeLog("Đã xóa file backup: $fileToDelete");
        echo "<div style='color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: .75rem 1.25rem; border-radius: .25rem; margin-bottom: 1rem;'>
              <strong>Đã xóa file backup: " . htmlspecialchars($fileToDelete) . "</strong>
              </div>";
        header("Refresh: 2; url=backup.php");
    } else {
        writeLog("Không tìm thấy hoặc file không hợp lệ: $fileToDelete");
        echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
              <strong>File không tồn tại hoặc không hợp lệ.</strong>
              </div>";
        header("Refresh: 4; url=backup.php");
    }
}
?>
