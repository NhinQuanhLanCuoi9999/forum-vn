<?php
include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

$backupFolder = $_SERVER['DOCUMENT_ROOT'] . '/backup/';
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/backup.txt';

// Nếu có tham số delete trong URL
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $filePath = $backupFolder . $fileToDelete;

    if (file_exists($filePath)) {
        unlink($filePath);
        writeLog("Đã xóa file backup: $fileToDelete");
        echo "<div style='color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: .75rem 1.25rem; border-radius: .25rem; margin-bottom: 1rem;'>
              <strong>Đã xóa file backup: " . $fileToDelete . "</strong>
              </div>";
              header("Refresh: 2; url=backup.php");
    } else {
        writeLog("Không tìm thấy file để xóa: $fileToDelete");
        echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
              <strong>File không tồn tại.</strong>
              </div>";
              header("Refresh: 4; url=backup.php");
    }
} 

?>
