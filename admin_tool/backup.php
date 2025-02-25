<?php
session_start();
date_default_timezone_set('Asia/Ho_Chi_Minh');
include '../app/backup/Pagination.php';
include '../app/backup/Logic.php';
include '../app/admin/logicPHP/Auth.php';
include '../app/backup/Delete.php';
include '../app/backup/Import.php';
include '../app/backup/Check2FA.php';



// Include file cấu hình CSDL
include '../config.php';



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
    $logMessage = "$date | IP: $ip | Người dùng: $username | Trạng thái: $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Backup & Import Database [BETA]</title>
    <link rel="stylesheet" href="/asset/css/Bootstrap.min.css">
    <link href="/asset/css/Poppins.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/app/backup/style.css">
    <script>
        function confirmDelete(fileName) {if (confirm("Bạn có chắc muốn xóa file " + fileName + " không?")) {window.location.href = "?delete=" + fileName;}}
        function confirmImport(fileName) {return confirm("Chú ý: Dữ liệu hiện tại sẽ được ghi đè. Bạn có chắc muốn import file " + fileName + " không?");}
    </script>
</head>
<body>
    <div class="container">
        <h1 class="mb-4 text-center">Hệ thống Backup & Import Database [BETA]</h1>
        
        <!-- Thông báo kết quả -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $alertType; ?>">
                <?php echo $message; ?></div><?php endif; ?>

        <div class="row">
            <!-- Cột Backup -->
            <div class="col-md-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Backup Database</h4>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <button type="submit" name="backup" class="btn btn-primary btn-block mb-3">Thực hiện Backup</button>
                        </form>
                        <h5>Danh sách file backup</h5>
                        <ul class="list-group backup-list">
            
                           <?php
                            if ($filesForPage) {
                                foreach ($filesForPage as $file) {
                                    $fileName = basename($file);
                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                    echo $fileName;
                                    echo '<button class="btn btn-danger btn-sm" onclick="confirmDelete(\'' . $fileName . '\')">Xóa</button>';
                                    echo '</li>';}} 
                            else {echo '<li class="list-group-item">Không có file backup nào.</li>';}
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
                            <button type="submit" name="import" class="btn btn-warning btn-block">Thực hiện Import</button>
                            <br>
                            <small class="form-text text-muted">Chú ý: Dữ liệu hiện tại sẽ được ghi đè bởi dữ liệu trong file import.</small>
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
