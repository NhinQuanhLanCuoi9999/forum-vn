<?php
include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

// Thiết lập thư mục backup
$backupFolder = $_SERVER['DOCUMENT_ROOT'] . '/backup/';
if (!is_dir($backupFolder)) {
    mkdir($backupFolder, 0755, true);
}
$randomHex = bin2hex(random_bytes(16)); // Sinh 32 ký tự hex ngẫu nhiên (16 byte)

// Đường dẫn file log
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/backup.txt';

// Nếu nhấn nút Backup
if (isset($_POST['backup'])) {
    // Lấy file backup cũ nhất (mới nhất) để rollback nếu cần
    $previousBackup = null;
    $files = glob($backupFolder . "*.sql");
    if ($files) {
        // Sắp xếp theo thời gian chỉnh sửa giảm dần
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        $previousBackup = $files[0];
    }

    // Thực hiện backup mới
    $dbName = "database_name";
    $date = date("d-m-Y_H-i-s");
    $backupFile = $backupFolder . $date . "_" . $randomHex . ".sql";

    $backupSQL = "-- Backup Database: {$dbName}\n";
    $backupSQL .= "-- Date: " . date("Y-m-d H:i:s") . "\n\n";

    // Lấy danh sách các bảng
    $tables = array();
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
    } else {
        writeLog("Thất bại khi backup, không thể lấy danh sách bảng.");
        echo "<div class='alert alert-danger'>Backup thất bại.</div>";
        exit();
    }

    foreach ($tables as $table) {
        // Lấy lệnh tạo bảng
        $result2 = $conn->query("SHOW CREATE TABLE $table");
        if ($result2) {
            $row2 = $result2->fetch_assoc();
            $createTable = $row2["Create Table"];
            $backupSQL .= "-- Tạo bảng $table\n";
            $backupSQL .= "DROP TABLE IF EXISTS $table;\n";
            $backupSQL .= $createTable . ";\n\n";
        } else {
            writeLog("Thất bại khi backup bảng: $table");
            echo "<div class='alert alert-danger'>Backup thất bại.</div>";
            exit();
        }

        // Lấy dữ liệu của bảng
        $result3 = $conn->query("SELECT * FROM $table");
        if ($result3) {
            while ($row = $result3->fetch_assoc()) {
                $cols = array();
                $vals = array();
                foreach ($row as $col => $val) {
                    $cols[] = $col;
                    // Escape giá trị để an toàn
                    $vals[] = "'" . $conn->real_escape_string($val) . "'";
                }
                $cols = implode(", ", $cols);
                $vals = implode(", ", $vals);
                $backupSQL .= "INSERT INTO $table ($cols) VALUES ($vals);\n";
            }
            $backupSQL .= "\n";
        } else {
            writeLog("Thất bại khi lấy dữ liệu bảng: $table");
            echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                  <strong>Backup thất bại.</strong>
                  </div>";
            header("Refresh: 4; url=backup.php");
            exit();
        }
    }

    // Ghi nội dung backup vào file
    if (file_put_contents($backupFile, $backupSQL) === false) {
        writeLog("Thất bại khi ghi backup vào file: $backupFile");
        echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
              <strong>Backup thất bại.</strong></div>";
        header("Refresh: 4; url=backup.php");
        exit();
    }

    writeLog("Backup thành công: " . basename($backupFile));
    echo "<div style='color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: .75rem 1.25rem; border-radius: .25rem; margin-bottom: 1rem;'>
          <strong>Backup thành công: " . basename($backupFile) . "</strong></div>";
    header("Refresh: 2; url=backup.php");
    exit();
}
?>
