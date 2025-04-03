<?php
include 'FileSizeHandle.php';
// Kiểm tra nếu tổng nội dung POST vượt quá giới hạn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_LENGTH'])) {
    $uploadedSize = (int)$_SERVER['CONTENT_LENGTH'];
    
    if ($uploadedSize > $maxPostSize) {
        $_SESSION['error'] = "File tải lên vượt quá giới hạn của server. Dung lượng file: " . formatSize($uploadedSize) . ". Giới hạn tối đa: " . formatSize($maxPostSize) . ".";
        header("Location: /");
        exit;
    }
}

  
// Kiểm tra CSRF token
$hasError = false; // Biến kiểm tra có lỗi không
$errorMessages = []; // Mảng lưu lỗi

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post'])) {
    if (!isset($_POST['csrf_token2']) || $_POST['csrf_token2'] !== $_SESSION['csrf_token2']) {
        $errorMessages[] = "❌ Yêu cầu không hợp lệ.";
        $hasError = true;
    }

    $content = trim($_POST['content']);
    $description = trim($_POST['description']);
    $username = $_SESSION['username'];
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // Kiểm tra content (không rỗng, tối đa 500 ký tự)
    if (empty($content)) {
        $errorMessages[] = "❌ Lỗi: Nội dung không được để trống.";
        $hasError = true;
    } elseif (strlen($content) > 500) {
        $errorMessages[] = "❌ Lỗi: Nội dung không được quá 500 ký tự (Hiện tại: " . strlen($content) . ").";
        $hasError = true;
    }

    // Kiểm tra description (không rỗng, tối đa 4096 ký tự)
    if (empty($description)) {
        $errorMessages[] = "❌ Lỗi: Mô tả không được để trống.";
        $hasError = true;
    } elseif (strlen($description) > 4096) {
        $errorMessages[] = "❌ Lỗi: Mô tả không được quá 4096 ký tự (Hiện tại: " . strlen($description) . ").";
        $hasError = true;
    }

    // Kiểm tra cooldown 10 phút
    if (isset($_SESSION['post_cooldown'])) {
        $remaining_time = $_SESSION['post_cooldown'] - time();
        if ($remaining_time > 0) {
            $errorMessages[] = "⏳ Bạn chỉ có thể đăng bài sau " . ceil($remaining_time / 60) . " phút nữa.";
            $hasError = true;
        }
    }


  // Kiểm tra nếu có file được upload
$newFileName = null; // Khởi tạo biến cho tên tệp mới
if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['file'];
    $fileName = pathinfo($file['name'], PATHINFO_FILENAME); // Lấy tên file gốc (không có đuôi)
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Cho phép upload mọi loại file ngoại trừ .php và .exe
    if (in_array($fileExt, ['php', 'exe'])) {
        $errorMessages[] = "❌ Bạn không thể tải lên loại tệp này.";
        $hasError = true;
    }
     elseif ($fileError !== 0) {
        $errorMessages[] = "❌ Có lỗi khi tải tệp: $fileError.";
        $hasError = true;
    } else {
        // Tạo mã random gồm 10 ký tự a-z, A-Z
        $randomString = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);

        // Đổi tên file theo format: <tên gốc>_<mã random>.<đuôi file>
        $newFileName = $fileName . '_' . $randomString . '.' . $fileExt;
        $fileDestination = 'uploads/' . $newFileName;

        // Di chuyển tệp đến thư mục uploads
        if (!move_uploaded_file($fileTmpName, $fileDestination)) {
            $errorMessages[] = "❌ Đã xảy ra lỗi khi tải tệp lên.";
            $hasError = true;
        }
    }
}

    // Nếu có lỗi, in lỗi ra màn hình và không thực thi tiếp
    if ($hasError) {
        foreach ($errorMessages as $error) {
            echo "<p style='color: red; font-weight: bold;'>$error</p>";
        }
    } else {
        // Nếu không có lỗi thì lưu vào database
        $stmt = $conn->prepare("INSERT INTO posts (content, description, file, username) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $content, $description, $newFileName, $_SESSION['username']);

        if ($stmt->execute()) {
            logAction("📝 Đăng bài mới của: {$_SESSION['username']}");

            // Đặt cooldown 10 phút
            $_SESSION['post_cooldown'] = time() + 600; 

            echo "<p style='color: green; font-weight: bold;'>✅ Bài viết đã được đăng thành công.</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ Có lỗi xảy ra khi lưu bài viết.</p>";
        }
    }
}

// Tạo CSRF token nếu chưa có
if (!isset($_SESSION['csrf_token2'])) {
    $_SESSION['csrf_token2'] = bin2hex(random_bytes(32));
}
?>
