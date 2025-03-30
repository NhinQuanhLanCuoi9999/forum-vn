<?php
include($_SERVER['DOCUMENT_ROOT'] . '/config.php');

$backupFolder = $_SERVER['DOCUMENT_ROOT'] . '/backup/';
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/admin/backup.txt';

// Nếu nhấn nút Import
if (isset($_POST['import'])) {
    if (!empty($_POST['import_file'])) {
        $selectedFile = basename($_POST['import_file']);
        $importFile = $backupFolder . $selectedFile;

        if (file_exists($importFile)) {
            $sql = file_get_contents($importFile);
            if ($sql !== false) {
                // Tắt kiểm tra khóa ngoại để tránh lỗi khi xóa bảng
                $conn->query("SET FOREIGN_KEY_CHECKS=0;");

                // Chia nhỏ các câu lệnh SQL
                $queries = preg_split("/;[\r\n]+/", $sql);
                foreach ($queries as $query) {
                    $query = trim($query);
                    if (!empty($query)) {
                        if (!$conn->query($query)) {
                            $errorMsg = $conn->error;
                            // Bật lại kiểm tra khóa ngoại trước khi thoát
                            $conn->query("SET FOREIGN_KEY_CHECKS=1;");
                            writeLog("Import thất bại từ file: $selectedFile - Lỗi: $errorMsg");
                            echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                                  <strong>Import thất bại, nếu gặp lỗi bạn có thể vào MySQL để xóa bảng và thực thi lại: $errorMsg</strong>
                                  </div>";
                            header("Refresh: 4; url=backup.php");
                            exit();
                        }
                    }
                }
                // Bật lại kiểm tra khóa ngoại
                $conn->query("SET FOREIGN_KEY_CHECKS=1;");
                writeLog("Import thành công từ file: $selectedFile");
                echo "<div style='color: #155724;background-color: #d4edda;border: 1px solid #c3e6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                      <strong>Import thành công từ file: " . $selectedFile . "</strong>
                      </div>";
                header("Refresh: 2; url=backup.php");
                exit();
            } else {
                writeLog("Không thể đọc file backup: $selectedFile");
                echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                      <strong>Không thể đọc file backup.</strong>
                      </div>";
                header("Refresh: 4; url=backup.php");
                exit();
            }
        } else {
            writeLog("File import không tồn tại: $selectedFile");
            echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
                  <strong>File import không tồn tại.</strong>
                  </div>";
            header("Refresh: 4; url=backup.php");
            exit();
        }
    } else {
        writeLog("Không có file import được chọn.");
        echo "<div style='color: #721c24;background-color: #f8d7da;border: 1px solid #f5c6cb;padding: .75rem 1.25rem;border-radius: .25rem;margin-bottom: 1rem;'>
              <strong>Vui lòng chọn file để import.</strong>
              </div>";
        header("Refresh: 4; url=backup.php");
        exit();
    }
}
?>
