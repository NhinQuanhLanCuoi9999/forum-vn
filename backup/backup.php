<?php
session_start();

date_default_timezone_set('Asia/Ho_Chi_Minh');

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_SESSION['username'];
}

// Kiểm tra xem người dùng có phải là admin không
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    header("Location: ../404.php");
    exit();
}
// Include file cấu hình CSDL, trong file này bạn đã thiết lập $conn = new mysqli(...)
include '../config.php';

$backupFolder = $_SERVER['DOCUMENT_ROOT'] . '/backup/';
if (!is_dir($backupFolder)) {
    mkdir($backupFolder, 0755, true);
}
$randomHex = bin2hex(random_bytes(16)); // Sinh 32 ký tự hex ngẫu nhiên (16 byte)

// Đường dẫn log
$logFile = $_SERVER['DOCUMENT_ROOT'] . '/logs/backup.txt';

// Hàm ghi log
function writeLog($message) {
    global $logFile;
    $date = date("d/m/Y | H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'guest';

    // Kiểm tra nếu message chứa phần tên file
    if (preg_match('/\w{8}\.sql$/', $message)) { // Kiểm tra tên file kết thúc bằng .sql
        $fileName = basename($message); // Lấy tên file
        if (strlen($fileName) > 8) {
            // Ẩn 8 ký tự cuối của tên file
            $hiddenFileName = substr($fileName, 0, strlen($fileName) - 8) . 'xxxxxxxx.sql';
            // Thay thế tên file trong message
            $message = str_replace($fileName, $hiddenFileName, $message);
        }
    }

    // Tạo thông điệp log
    $logMessage = "[$date | IP: $ip | Người dùng: $username] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}


// Khởi tạo thông báo
$message = "";
$alertType = "success";

// Xử lý khi nhấn nút Backup
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
            echo "<div class='alert alert-danger'>$message</div>";
            exit();
        }
    }

    // Ghi nội dung backup vào file
    if (file_put_contents($backupFile, $backupSQL) === false) {
        $alertType = "danger";
        $message = "Backup thất bại.";
        writeLog("Thất bại khi ghi backup vào file: $backupFile");
        echo "<div class='alert alert-danger'>$message</div>";
        exit();
    }

    $message = "Backup thành công: " . basename($backupFile);
    writeLog("Backup thành công: " . basename($backupFile));

    // Bỏ qua phần rollback
    // Không cần thực hiện rollback nữa, chỉ cần thông báo backup thành công.
}

// Xử lý Import từ file được chọn (file có sẵn trong thư mục /backup/)
if (isset($_POST['import'])) {
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
                            $message = "Import thất bại: " . $errorMsg;
                            writeLog("Import thất bại từ file: $selectedFile - Lỗi: $errorMsg");
                            echo "<div class='alert alert-danger'>$message</div>";
                            exit();
                        }
                    }
                }
                // Bật lại kiểm tra khóa ngoại
                $conn->query("SET FOREIGN_KEY_CHECKS=1;");
                $message = "Import thành công từ file: " . $selectedFile;
                writeLog("Import thành công từ file: $selectedFile");
            } else {
                $alertType = "danger";
                $message = "Không thể đọc file backup.";
                writeLog("Không thể đọc file backup: $selectedFile");
                echo "<div class='alert alert-danger'>$message</div>";
            }
        } else {
            $alertType = "danger";
            $message = "File import không tồn tại.";
            writeLog("File import không tồn tại: $selectedFile");
            echo "<div class='alert alert-danger'>$message</div>";
        }
    } else {
        $alertType = "danger";
        $message = "Vui lòng chọn file để import.";
        writeLog("Không có file import được chọn.");
        echo "<div class='alert alert-danger'>$message</div>";
    }
}

// Xử lý yêu cầu xóa file backup (truyền qua tham số GET delete)
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $filePath = $backupFolder . $fileToDelete;

    if (file_exists($filePath)) {
        unlink($filePath);
        $message = "Đã xóa file backup: " . $fileToDelete;
        writeLog("Đã xóa file backup: $fileToDelete");
    }
}
?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Backup & Import Database [BETA]</title>
    <link rel="stylesheet" href="/asset/css/Bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .container { margin-top: 30px; }
        .backup-list li { margin-bottom: 8px; }
        .card { margin-bottom: 20px; }
    </style>
    <script>
        function confirmDelete(fileName) {
            if (confirm("Bạn có chắc muốn xóa file " + fileName + " không?")) {
                window.location.href = "?delete=" + fileName;
            }
        }
        function confirmImport(fileName) {
            return confirm("Chú ý: Dữ liệu hiện tại sẽ được ghi đè. Bạn có chắc muốn import file " + fileName + " không?");
        }
    </script>
</head>
<body>
    <div class="container">
        <h1 class="mb-4 text-center">Hệ thống Backup & Import Database [BETA]</h1>
        
        <!-- Thông báo kết quả -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $alertType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Cột Backup -->
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Backup Database</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <button type="submit" name="backup" class="btn btn-primary btn-block mb-3">
                                Thực hiện Backup
                            </button>
                        </form>
                        <h5>Danh sách file backup</h5>
                        <ul class="list-group backup-list">
                            <?php
                            // Số bản ghi mỗi trang
                            $recordsPerPage = 7;

                            // Lấy danh sách file
                            $files = glob($backupFolder . "*.sql");

                            // Sắp xếp file theo thời gian chỉnh sửa, mới nhất trước
                            usort($files, function($a, $b) {
                                return filemtime($b) - filemtime($a);
                            });

                            $totalRecords = count($files);
                            $totalPages = ceil($totalRecords / $recordsPerPage);
                            $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $startIndex = ($currentPage - 1) * $recordsPerPage;
                            $filesForPage = array_slice($files, $startIndex, $recordsPerPage);

                            if ($filesForPage) {
                                foreach ($filesForPage as $file) {
                                    $fileName = basename($file);
                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                    echo $fileName;
                                    echo '<button class="btn btn-danger btn-sm" onclick="confirmDelete(\'' . $fileName . '\')">Xóa</button>';
                                    echo '</li>';
                                }
                            } else {
                                echo '<li class="list-group-item">Không có file backup nào.</li>';
                            }
                            ?>
                        </ul>
                        
                        <!-- Phân trang -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=1"><<<</a></li>
                                <?php endif; ?>

                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $currentPage - 1 ?>">&#8249;</a></li>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $currentPage - 3); // Bắt đầu từ 3 trang trước
                                $endPage = min($totalPages, $currentPage + 3); // Kết thúc ở 3 trang sau

                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $currentPage + 1 ?>">&#8250;</a></li>
                                <?php endif; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>">>>></a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Cột Import -->
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">Import Database</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" onsubmit="return confirmImport(document.getElementById('import_file').value)">
                            <div class="form-group">
                                <label for="import_file">Chọn file SQL từ thư mục /backup/</label>
                                <ul class="list-group">
                                    <?php foreach ($filesForPage as $file): ?>
                                        <li class="list-group-item">
                                            <input type="radio" name="import_file" id="import_file" value="<?= basename($file) ?>" required>
                                            <?= basename($file) ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <button type="submit" name="import" class="btn btn-warning btn-block">
                                Thực hiện Import
                            </button>
                            <small class="form-text text-muted">
                                Chú ý: Dữ liệu hiện tại sẽ được ghi đè bởi dữ liệu trong file import.
                            </small>
                        </form>

                        <!-- Phân trang -->
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=1"><<<</a></li>
                                <?php endif; ?>

                                <?php if ($currentPage > 1): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $currentPage - 1 ?>">&#8249;</a></li>
                                <?php endif; ?>

                                <?php
                                $startPage = max(1, $currentPage - 3); // Bắt đầu từ 3 trang trước
                                $endPage = min($totalPages, $currentPage + 3); // Kết thúc ở 3 trang sau

                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= ($i == $currentPage) ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $currentPage + 1 ?>">&#8250;</a></li>
                                <?php endif; ?>

                                <?php if ($currentPage < $totalPages): ?>
                                    <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>">>>></a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="/asset/js/jquery.min.js"></script>
    <script src="/asset/js/Bootstrap.bundle.min.js"></script>
</body>
</html>
