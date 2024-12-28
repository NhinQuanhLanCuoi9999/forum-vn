<?php
// Đăng bài
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post'])) {
    $content = $_POST['content'];
    $description = $_POST['description'];
    $username = $_SESSION['username'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Kiểm tra nếu nội dung không rỗng và có chứa từ cấm
    if ((!empty($content) && containsBadWords(content: $content)) || 
        (!empty($description) && containsBadWords(content: $description))) {
        $_SESSION['error_message'] = "Nội dung không phù hợp, vui lòng kiểm tra lại.";
        header(header: "Location: index.php");
        exit();
    }

  
// Kiểm tra nếu đang trong thời gian chờ (10 phút) để đăng bài
    if (isset($_SESSION['post_cooldown'])) {
        $remaining_time = $_SESSION['post_cooldown'] - time();
        if ($remaining_time > 0) {
            $_SESSION['error_message'] = "Bạn chỉ có thể đăng bài sau " . ceil($remaining_time / 60) . " phút nữa.";
            header("Location: index.php");
            exit();
        }
    }  

    // Kiểm tra có emoji trong nội dung và mô tả
    if (preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content) || 
        preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $description)) {
        $_SESSION['error'] = "Nội dung và mô tả không được chứa emoji.";
        header("Location: index.php");
        exit();
    }$newFileName = null; // Khởi tạo biến cho tên tệp mới

    // Kiểm tra nếu có tệp tin được tải lên
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
        // Các loại tệp được phép
        $allowedExt = [
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rar', 'zip', 'html', 'css', 'js', 'java', 'php', 'py', 
            'lua', 'c', 'cpp', 'pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'mp4', 'avi', 'mkv', 'mov', 'mp3', 'wav',
            'ogg', 'flac', 'sql', 'json', 'xml', 'yaml', 'md', 'rtf', 'epub', 'mobi', 'apk', 'exe', 'bin', 'iso', 'torrent', 
            'csv', 'psd', 'ai', 'svg', 'webp', 'ogg', 'flv', 'zip', '7z', 'tar', 'gz', 'rar', 'mpg', 'mpeg', 'bmp', 'ttf', 'otf',
            'woff', 'woff2', 'eot', 'scss', 'less', 'tsx', 'jsx', 'ts', 'dart', 'coffee', 'asm', 'bat', 'sh', 'ps1', 'vbs', 'pl'
        ];
        // Kiểm tra loại tệp
        $allowedTypes = [
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
            'application/vnd.powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 
            'text/plain', 'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 
            'text/html', 'text/css', 'application/javascript', 'text/javascript', 'application/x-httpd-php', 
            'text/x-python', 'application/x-lua', 'text/x-c', 'text/x-c++', 'application/pdf', 'image/jpeg', 
            'image/png', 'image/gif', 'image/bmp', 'image/tiff', 'video/mp4', 'audio/mp3', 'audio/wav', 'audio/ogg',
            'application/sql', 'application/json', 'application/xml', 'application/yaml', 'application/epub+zip', 
            'application/epub+zip', 'application/x-msdownload', 'application/octet-stream', 'application/x-tar', 
            'application/x-gzip', 'application/x-bzip2', 'application/x-rar', 'application/x-shockwave-flash', 
            'application/x-iso9660-image', 'application/x-bittorrent', 'text/csv', 'image/vnd.adobe.photoshop', 
            'application/postscript', 'image/svg+xml', 'application/ogg', 'video/quicktime', 'application/javascript',
            'text/x-shellscript', 'application/x-asp', 'application/x-perl', 'application/x-python', 'text/x-perl', 
            'application/vnd.ms-fontobject', 'font/woff', 'font/woff2', 'application/vnd.ms-fontobject', 'font/otf', 
            'font/ttf', 'application/x-font-woff2', 'application/font-woff', 'font/woff', 'application/x-woff'
        ];
        // Kiểm tra loại tệp và kích thước tệp (tối đa 5MB)
        if (!in_array($fileExt, $allowedExt) || !in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = "Bạn chỉ có thể đăng các tệp : Văn phòng , mã nguồn , tệp nén.";
            header("Location: index.php");
            exit();
        } elseif ($fileSize > 5 * 1024 * 1024) {
            $_SESSION['error'] = "Bạn chỉ có thể đăng tệp dưới 5 MB.";
            header("Location: index.php");
            exit();
        } elseif ($fileError !== 0) {
            $_SESSION['error'] = "Có lỗi khi tải tệp: $fileError.";
            header("Location: index.php");
            exit();
        } else {
            // Tạo tên tệp mới để tránh trùng lặp
            $newFileName = uniqid('', true) . '.' . $fileExt;
            $fileDestination = 'uploads/' . $newFileName;
    
            // Di chuyển tệp đến thư mục uploads
            if (!move_uploaded_file($fileTmpName, $fileDestination)) {
                $_SESSION['error'] = "Đã xảy ra lỗi khi tải tệp lên.";
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['success'] = "Tệp đã được tải lên thành công.";
            }
        }
    }
    
    // Lưu bài viết vào cơ sở dữ liệu sau khi tải tệp thành công hoặc không có tệp
    $stmt = $conn->prepare("INSERT INTO posts (content, description, file, username) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $content, $description, $newFileName, $_SESSION['username']);
    
    if ($stmt->execute()) {
        // Log hành động của người dùng
        logAction("Đăng bài mới của người dùng: {$_SESSION['username']}");
    
        // Đặt thời gian chờ (10 phút) trong session
        $_SESSION['post_cooldown'] = time() + 600; // 600 giây = 10 phút
    
        // Sau khi lưu bài viết thành công
        $_SESSION['success'] = "Bài viết đã được đăng thành công.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra khi lưu bài viết.";
        header("Location: index.php");
        exit();
    }
}
?>    