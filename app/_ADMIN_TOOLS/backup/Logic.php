<?php
include($_SERVER['DOCUMENT_ROOT'] . '/config.php'); 
require_once $_SERVER['DOCUMENT_ROOT'] . '/app/vendor/autoload.php';

use Spatie\DbDumper\Databases\MySql;

// Thiết lập thư mục backup
$backupFolder = $_SERVER['DOCUMENT_ROOT'] . '/backup/';
if (!is_dir($backupFolder)) {
    mkdir($backupFolder, 0755, true);
}

$randomHex = bin2hex(random_bytes(28));
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/backup.txt';


if (isset($_POST['backup'])) {
    $date = date("d-m-Y_H-i-s");
    $backupFile = $backupFolder . $date . "_" . $randomHex . ".sql";

    try {
        // Dùng Spatie dump database ngay luôn, không cần vòng lặp từng bảng
        MySql::create()
            ->setHost($host)
            ->setDbName($db)
            ->setUserName($user)
            ->setPassword($pass)
            ->dumpToFile($backupFile);

        writeLog("Backup thành công: " . basename($backupFile));
        echo "<div style='color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: .75rem 1.25rem; border-radius: .25rem; margin-bottom: 1rem;'>
              <strong>Backup thành công: " . basename($backupFile) . "</strong></div>";
        header("Refresh: 2; url=backup.php");
        exit();
    } catch (Exception $e) {
        writeLog("Backup thất bại: " . $e->getMessage());
        echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
              <strong>Backup thất bại.</strong></div>";
        header("Refresh: 4; url=backup.php");
        exit();
    }
}
?>
