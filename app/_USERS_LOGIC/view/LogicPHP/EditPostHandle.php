<?php
include_once 'RateLimit.php';
require $_SERVER['DOCUMENT_ROOT'] . '/app/_USERS_LOGIC/index/logicPHP/FileSizeHandle.php';


// Kiểm tra nếu tổng nội dung POST vượt quá giới hạn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_LENGTH'])) {
    $uploadedSize = (int)$_SERVER['CONTENT_LENGTH'];
    
    if ($uploadedSize > $maxPostSize) {
        $_SESSION['error'] = "File tải lên vượt quá giới hạn của server. Dung lượng file: " . formatSize($uploadedSize) . ". Giới hạn tối đa: " . formatSize($maxPostSize) . ".";
        header("Location: view.php?id=" . urlencode($postId) . "&page=" . urlencode($page));
        exit;
    }
}

/**
 * Hàm ghi log chỉnh sửa bài đăng với nội dung thay đổi cụ thể
 * @param int $postId ID bài đăng
 * @param string $changeDetails Các thay đổi được ghi log
 */
function logEditPost($postId, $changeDetails) {
    $logFile = '../logs/edit.txt';
    $date = date('d/m/Y | H:i:s');
    $username = $_SESSION['username'];
    $logMessage = "[$date] [$username] đã cập nhật bài đăng ID=$postId: $changeDetails\n";
    
    // Ghi log vào file, nếu thất bại thì gán lỗi vào session
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        $_SESSION['error'] = "Không thể ghi log vào file!";
    }
}

// Lấy giới hạn file upload từ php.ini
$maxFileSize = min(
    (int)(ini_get('upload_max_filesize')) * 1024 * 1024,
    (int)(ini_get('post_max_size')) * 1024 * 1024
);

// Xử lý cập nhật bài đăng khi nhận được form edit_post
if (isset($_POST['edit_post']) && $isOwner) {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Token không hợp lệ.";
        header("Location: view.php?id=" . urlencode($postId) . "&page=" . urlencode($page));
        exit;
    }

    // Kiểm tra giới hạn chỉnh sửa
    checkRateLimit($postId);

   // Lấy dữ liệu từ form
$newContent = trim($_POST['content']);
$newDescription = trim($_POST['description']);
$newFile = $post['file']; // Giữ file cũ nếu không có upload mới

// Nếu có file mới được upload
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileSize = $_FILES['file']['size'];

    // Kiểm tra kích thước file so với giới hạn
    if ($fileSize > $maxFileSize) {
        $_SESSION['error'] = "File quá lớn! Giới hạn tối đa là " . ($maxFileSize / 1024 / 1024) . "MB.";
        header("Location: view.php?id=" . urlencode($postId) . "&page=" . urlencode($page));
        exit;
    }

    $uploadDir = '../uploads/';
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileExt = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    $fileName = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME); // Lấy tên file gốc (không có đuôi)

    // Tạo mã random gồm 10 ký tự a-z, A-Z
    $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);

    // Tạo tên file mới theo format: <tên gốc>_<mã random>.<đuôi file>
    $newFileName = $fileName . '_' . $randomString . '.' . $fileExt;
    $filePath = $uploadDir . $newFileName;

    // Di chuyển file từ temp sang thư mục uploads
    if (move_uploaded_file($fileTmpPath, $filePath)) {
        $newFile = $newFileName;
    } else {
        $_SESSION['error'] = "Upload file thất bại.";
        header("Location: view.php?id=" . urlencode($postId) . "&page=" . urlencode($page));
        exit;
    }
}

    
    // Kiểm tra dữ liệu rỗng
    if (empty($newContent)) {
        $_SESSION['error'] = "Nội dung bài đăng không được để trống.";
        header("Location: view.php?id=" . urlencode($postId) . "&page=" . urlencode($page));
        exit;
    }
    
    // Thực hiện UPDATE vào database
    $stmt = $conn->prepare("UPDATE posts SET content = ?, description = ?, file = ? WHERE id = ? AND username = ?");
    if ($stmt) {
        $stmt->bind_param("sssis", $newContent, $newDescription, $newFile, $postId, $_SESSION['username']);
        if ($stmt->execute()) {
            // So sánh thay đổi và chuẩn bị log chi tiết
            $changeDetails = "";
            if ($newContent !== $post['content']) {
                $changeDetails .= "Nội dung cập nhật: \"$newContent\". ";
            }
            if ($newDescription !== $post['description']) {
                $changeDetails .= "Mô tả cập nhật: \"$newDescription\". ";
            }
            if ($newFile !== $post['file']) {
                $changeDetails .= "File cập nhật: \"$newFile\". ";
            }
            if ($changeDetails === "") {
                $changeDetails = "Không có thay đổi nào.";
            }
            
            // Ghi log chỉnh sửa bài đăng với chi tiết thay đổi
            logEditPost($postId, $changeDetails);
            $_SESSION['success'] = "Cập nhật bài đăng thành công!";
        } else {
            $_SESSION['error'] = "Lỗi cập nhật bài đăng: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Lỗi truy vấn: " . $conn->error;
    }
    
    // Redirect về trang view bài đăng
    header("Location: view.php?id=" . urlencode($postId) . "&page=" . urlencode($page));
    exit;
}
?>