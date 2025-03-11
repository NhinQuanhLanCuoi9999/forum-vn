<?php

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

    // Kiểm tra content (không rỗng, tối đa 222 ký tự)
    if (empty($content)) {
        $errorMessages[] = "❌ Lỗi: Nội dung không được để trống.";
        $hasError = true;
    } elseif (strlen($content) > 222) {
        $errorMessages[] = "❌ Lỗi: Nội dung không được quá 222 ký tự (Hiện tại: " . strlen($content) . ").";
        $hasError = true;
    }

    // Kiểm tra description (không rỗng, tối đa 525 ký tự)
    if (empty($description)) {
        $errorMessages[] = "❌ Lỗi: Mô tả không được để trống.";
        $hasError = true;
    } elseif (strlen($description) > 525) {
        $errorMessages[] = "❌ Lỗi: Mô tả không được quá 525 ký tự (Hiện tại: " . strlen($description) . ").";
        $hasError = true;
    }

    // Kiểm tra từ cấm
    if ((!empty($content) && containsBadWords($content)) || 
        (!empty($description) && containsBadWords($description))) {
        $errorMessages[] = "❌ Nội dung không phù hợp, vui lòng kiểm tra lại.";
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

    // Kiểm tra emoji
    if (preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content) || 
        preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $description)) {
        $errorMessages[] = "❌ Nội dung và mô tả không được chứa emoji.";
        $hasError = true;
    }

    // Kiểm tra nếu có file được upload
    $newFileName = null; // Khởi tạo biến cho tên tệp mới
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Cho phép upload mọi loại file ngoại trừ .php và .exe
        if (in_array($fileExt, ['php', 'exe'])) {
            $errorMessages[] = "❌ Bạn không thể tải lên loại tệp này.";
            $hasError = true;
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $errorMessages[] = "❌ Bạn chỉ có thể tải lên tệp dưới 5 MB.";
            $hasError = true;
        } elseif ($fileError !== 0) {
            $errorMessages[] = "❌ Có lỗi khi tải tệp: $fileError.";
            $hasError = true;
        } else {
            // Tạo tên tệp mới để tránh trùng lặp
            $newFileName = uniqid('', true) . '.' . $fileExt;
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
