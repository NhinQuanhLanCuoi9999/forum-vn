<?php
/*
                        ĐÂY LÀ PHẦN LOGIC CỦA BACKUP
*/
include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

$backupFolder = $_SERVER['DOCUMENT_ROOT'] . '/backup/';
if (!is_dir($backupFolder)) {
    mkdir($backupFolder, 0755, true);
}
$randomHex = bin2hex(random_bytes(16)); // Sinh 32 ký tự hex ngẫu nhiên (16 byte)

// Đường dẫn log
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/backup.txt';

// Khởi tạo thông báo
$message = "";
$alertType = "success";

// Biến kiểm tra thao tác
$actionPerformed = false;

// Xử lý khi nhấn nút Backup
if (isset($_POST['backup'])) {
    $actionPerformed = true;
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
        $alertType = "danger";
        $message = "Backup thất bại.";
        writeLog("Thất bại khi backup, không thể lấy danh sách bảng.");
        echo "<div class='alert alert-danger'>$message</div>";
        exit();
    }

    foreach ($tables as $table) {
        // Lấy lệnh tạo bảng
        $result2 = $conn->query("SHOW CREATE TABLE `$table`");
        if ($result2) {
            $row2 = $result2->fetch_assoc();
            $createTable = $row2["Create Table"];
            $backupSQL .= "-- Tạo bảng `$table`\n";
            $backupSQL .= "DROP TABLE IF EXISTS `$table`;\n";
            $backupSQL .= $createTable . ";\n\n";
        } else {
            $alertType = "danger";
            $message = "Backup thất bại.";
            writeLog("Thất bại khi backup bảng: $table");
            echo "<div class='alert alert-danger'>$message</div>";
            exit();
        }

        // Lấy dữ liệu của bảng
        $result3 = $conn->query("SELECT * FROM `$table`");
        if ($result3) {
            while ($row = $result3->fetch_assoc()) {
                $cols = array();
                $vals = array();
                foreach ($row as $col => $val) {
                    $cols[] = "`" . $col . "`";
                    // Escape giá trị để an toàn
                    $vals[] = "'" . $conn->real_escape_string($val) . "'";
                }
                $cols = implode(", ", $cols);
                $vals = implode(", ", $vals);
                $backupSQL .= "INSERT INTO `$table` ($cols) VALUES ($vals);\n";
            }
            $backupSQL .= "\n";
        } else {
            $alertType = "danger";
            $message = "Backup thất bại.";
            writeLog("Thất bại khi lấy dữ liệu bảng: $table");
            echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
            <strong>$message</strong>
            </div>";
            header("Refresh: 4; url=backup.php");
            exit();
        }
    }

    // Ghi nội dung backup vào file
    if (file_put_contents($backupFile, $backupSQL) === false) {
        $alertType = "danger";
        $message = "Backup thất bại.";
        writeLog("Thất bại khi ghi backup vào file: $backupFile");
        echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
        <strong>$message</strong></div>";
        header("Refresh: 4; url=backup.php");
        exit();
    }

    $message = "<div style='color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: .75rem 1.25rem; border-radius: .25rem; margin-bottom: 1rem;'><strong>Backup thành công: " . basename($backupFile) . "</strong></div>";
    writeLog("Backup thành công: " . basename($backupFile));
    header("Refresh: 4; url=backup.php");
}

/*
                        ĐÂY LÀ PHẦN LOGIC CỦA IMPORT
*/
if (isset($_POST['import'])) {
    $actionPerformed = true;
    if (!empty($_POST['import_file'])) {
        $selectedFile = basename($_POST['import_file']);
        $importFile = $backupFolder . $selectedFile;

        if (file_exists($importFile)) {
            $errorMsg = "";
            $sql = file_get_contents($importFile);
            if ($sql !== false) {
                // Tắt kiểm tra khóa ngoại để tránh lỗi ràng buộc khi xóa bảng
                $conn->query("SET FOREIGN_KEY_CHECKS=0;");

                // Chia nhỏ câu lệnh SQL để tránh lỗi khi import
                $queries = preg_split("/;[\r\n]+/", $sql);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        if (!$conn->query($query)) {
                            $errorMsg = $conn->error;
                            // Bật lại kiểm tra khóa ngoại trước khi thoát
                            $conn->query("SET FOREIGN_KEY_CHECKS=1;");
                            $alertType = "danger";
                            $message = "Import thất bại,nếu gặp lỗi bạn có thể vào MySQL để xóa bảng và thực thi lại.: " . $errorMsg;
                            writeLog("Import thất bại từ file: $selectedFile - Lỗi: $errorMsg");
                            echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                            <strong>$message</strong>
                            </div>";
                            header("Refresh: 4; url=backup.php");
                            exit();
                        }
                    }
                }
                // Bật lại kiểm tra khóa ngoại
                $conn->query("SET FOREIGN_KEY_CHECKS=1;");
                $message = "<div style='color: #155724;background-color: #d4edda;border: 1px solid #c3e6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                <strong>Import thành công từ file: " . $selectedFile . "</strong>
                </div>";
            
                writeLog("Import thành công từ file: $selectedFile");
                header("Refresh: 4; url=backup.php");
            } else {
                $alertType = "danger";
                $message = "Không thể đọc file backup.";
                writeLog("Không thể đọc file backup: $selectedFile");
                echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                <strong>$message</strong>
            </div>";
                header("Refresh: 4; url=backup.php");
            }
        } else {
            $alertType = "danger";
            $message = "File import không tồn tại.";
            writeLog("File import không tồn tại: $selectedFile");
            echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
            <strong>$message</strong>
        </div>";
            header("Refresh: 4; url=backup.php");
        }
    } else {
        $alertType = "danger";
        $message = "Vui lòng chọn file để import.";
        writeLog("Không có file import được chọn.");
        echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
        <strong>$message</strong>
    </div>";
        header("Refresh: 4; url=backup.php");
    }
}

/*
                        ĐÂY LÀ PHẦN LOGIC CỦA DELETE FILE BACKUP
*/
if (isset($_GET['delete'])) {
    $actionPerformed = true;
    $fileToDelete = basename($_GET['delete']);
    $filePath = $backupFolder . $fileToDelete;

    if (file_exists($filePath)) {
        unlink($filePath);
        $message = "<div style='color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: .75rem 1.25rem; border-radius: .25rem; margin-bottom: 1rem;'><strong>Đã xóa file backup: " . $fileToDelete . "</strong></div>";
        writeLog("Đã xóa file backup: $fileToDelete");
    }
}

// In thông báo
echo "<div class='$alertType'>$message</div>";

// Kiểm tra nếu có thao tác và thực hiện refresh lại trang
if ($actionPerformed) {
    // Xóa cache và refresh lại trang về /backup/backup.php
    header("Cache-Control: no-cache, no-store, must-revalidate"); // Hướng dẫn trình duyệt không sử dụng cache
    header("Pragma: no-cache"); // Thêm header để yêu cầu không cache
    header("Expires: 0"); // Đặt thời gian hết hạn là 0

    // Chuyển hướng về trang backup.php sau 2 giây
    header("Refresh: 1; url=/backup/backup.php");
    exit();
}
?>
