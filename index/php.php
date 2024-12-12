<?php
// Kiểm tra nếu người dùng đã xác thực captcha bằng cookie
if (!isset($_COOKIE['captcha_verified'])) {
    header("Location: captcha_verification.php"); // Chuyển hướng đến trang xác thực captcha
    exit();
}



// Kiểm tra nếu người dùng đã đăng nhập thông qua cookie
if (isset($_COOKIE['username']) && !isset($_SESSION['username'])) {
    $_SESSION['username'] = $_COOKIE['username'];
}

// Hiển thị nút chuyển hướng đến admin.php nếu username là 'admin'
if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    echo '<a href="admin.php" class="admin-button">Admin Panel</a>';
}

// Hàm ghi log
function logAction($action) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Khách';
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] - IP: $ip - Người dùng: $username - Hành động: $action\n";

    // Đường dẫn tới file log
    $log_dir = 'logs';
    $log_file = $log_dir . '/logs.txt';

    // Kiểm tra xem thư mục logs có tồn tại không, nếu không thì tạo mới
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true); // Tạo thư mục với quyền truy cập
    }

    // Ghi log vào file
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Thiết lập múi giờ cho Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Hàm định dạng nội dung
function formatText($text) {
    // Định dạng in đậm **text**
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text); 
    // Định dạng in nghiêng *text*
    $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text); 
    // Định dạng gạch ngang --text--
    $text = preg_replace('/--(.*?)--/', '<del>$1</del>', $text); 
    // Định dạng gạch chân __text__
    $text = preg_replace('/__(.*?)__/', '<u>$1</u>', $text);
    // Định dạng code inline `code`
    $text = preg_replace('/`(.*?)`/', '<code style="background-color: #f0f0f0; color: #333; padding: 2px 4px; border-radius: 3px;">$1</code>', $text);
    // Định dạng code block ```code```
    $text = preg_replace('/```(.*?)```/s', '<pre class="code-block">$1</pre>', $text);
    // Định dạng spoil block ||text|| với JavaScript và localStorage
    $text = preg_replace_callback('/\|\|(.*?)\|\|/', function ($matches) {
        $id = uniqid('spoil_'); // Tạo ID duy nhất cho mỗi spoil block
        return '<span id="' . $id . '" class="spoil" style="background-color: #333; color: #333; padding: 5px; cursor: pointer;" onclick="toggleSpoiler(this, \'' . $id . '\')">' . $matches[1] . '</span>';
    }, $text);
    return $text;
}
// Hàm kiểm tra từ cấm
function containsBadWords($content) {
    $badWords = file('badwords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($badWords as $word) {
        // Tạo pattern cho phép chỉ nhận diện từ cấm mà không bị dính với dấu cách hay dấu câu
        // Sử dụng \W để xác định ký tự không phải là chữ cái hoặc số
        $pattern = '/(?<![a-zA-Z0-9])' . preg_quote($word, '/') . '(?![a-zA-Z0-9])/iu';
        if (preg_match($pattern, $content)) {
            return true;
        }
    }
    return false;
}
// Đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra xem tên người dùng có phải là "admin" không
    if (strtolower($username) === 'admin') {
        // Không kiểm tra từ bậy bạ, có thể thực hiện các kiểm tra khác nếu cần
    } else {
        // Kiểm tra xem tên người dùng có chứa từ bậy bạ nào không
        $badwords = file('badwords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($badwords as $badword) {
            if (stripos($username, trim($badword)) !== false) {
                $_SESSION['error'] = "Không thể tạo tài khoản vì tên người dùng chứa từ không phù hợp. (" . htmlspecialchars($badword) . ")";
                header("Location: index.php");
                exit();
            }
        }
    }

    // Kiểm tra nếu hai mật khẩu không trùng khớp
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
        header("Location: index.php");
        exit();
    }

    // Kiểm tra tài khoản đã tồn tại
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $checkUser->bind_param("s", $username);
    $checkUser->execute();
    $result = $checkUser->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Tài khoản đã tồn tại!";
        header("Location: index.php");
        exit();
    }

    // Tạo tài khoản mới nếu không có lỗi
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashed_password);
    $stmt->execute();
    $_SESSION['success'] = "Đăng ký thành công!";
    logAction("Đăng ký tài khoản: $username");

    // Chuyển hướng về trang chính sau khi đăng ký thành công
    header("Location: index.php");
    exit();
}

// Đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;

            // Thiết lập cookie để ghi nhớ người dùng trong 30 ngày
            setcookie("username", $username, time() + (30 * 24 * 60 * 60), "/");
            logAction("Đăng nhập thành công: $username");

            // Chuyển hướng sau khi đăng nhập
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Mật khẩu không chính xác!";
        }
    } else {
        $_SESSION['error'] = "Tài khoản không tồn tại!";
    }
}
// Đăng bài
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post'])) {
    $content = $_POST['content'];
    $description = $_POST['description'];
    $username = $_SESSION['username'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Kiểm tra nếu nội dung không rỗng và có chứa từ cấm
    if ((!empty($content) && containsBadWords($content)) || 
        (!empty($description) && containsBadWords($description))) {
        $_SESSION['error_message'] = "Nội dung không phù hợp, vui lòng kiểm tra lại.";
        header("Location: index.php");
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
    }

    $newFileName = null; // Khởi tạo biến cho tên tệp mới

    // Kiểm tra nếu có tệp tin được tải lên
    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Các loại tệp được phép
        $allowedExt = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rar', 'zip', 'html', 'css', 'js', 'java', 'php', 'py'];

        // Kiểm tra loại tệp
        $allowedTypes = [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed',
            'text/html',
            'text/css',
            'application/javascript',
            'text/javascript',
            'application/x-httpd-php',
            'text/x-python',
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

    // Nếu không có tệp nào được tải lên, không cần thông báo lỗi
    // Chỉ cần tiếp tục và lưu bài viết vào cơ sở dữ liệu ở đây
    // Ví dụ: savePost($content, $description, $username, $ip_address, $newFileName);
    
    // Sau khi lưu bài viết thành công
    $_SESSION['success'] = "Bài viết đã được đăng thành công.";
    header("Location: index.php");
    exit();
}

    // Lưu bài viết vào cơ sở dữ liệu
    $stmt = $conn->prepare("INSERT INTO posts (content, description, file, username) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $content, $description, $newFileName, $_SESSION['username']);
    $stmt->execute();
    logAction("Đăng bài mới của người dùng: {$_SESSION['username']}");

    // Đặt thời gian chờ (10 phút) trong session
    $_SESSION['post_cooldown'] = time() + 600; // 600 giây = 10 phút

    // Chuyển hướng sau khi đăng bài để ngăn việc gửi form lặp lại
    header("Location: index.php");
    exit();
}



// Hiển thị thông báo lỗi nếu có
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
}

// Xóa bài viết
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Lấy tên tệp từ cơ sở dữ liệu
    $stmt = $conn->prepare("SELECT file FROM posts WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $id, $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $post = $result->fetch_assoc();
    
    if ($post) {
        // Xóa tệp tin nếu có
        if ($post['file'] && file_exists('uploads/' . $post['file'])) {
            unlink('uploads/' . $post['file']);
        }

        // Xóa bài viết
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        logAction("Xóa bài viết với ID: $id bởi người dùng: {$_SESSION['username']}");
    } else {
        $_SESSION['error'] = "Bạn không có quyền xóa bài viết này!";
    }

    // Chuyển hướng để làm mới trang
    header("Location: index.php");
    exit();
}

// Thêm bình luận
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    // Kiểm tra nếu nội dung bình luận không rỗng và có chứa từ cấm
    if (!empty($content) && containsBadWords($content)) {
        $_SESSION['error'] = "Nội dung không phù hợp, vui lòng kiểm tra lại.";
    } else {
        // Định dạng nội dung bình luận
        $formatted_content = formatText($content);

        $stmt = $conn->prepare("INSERT INTO comments (post_id, content, username) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $post_id, $formatted_content, $_SESSION['username']);
        $stmt->execute();
        logAction("Thêm bình luận vào bài viết ID: $post_id bởi người dùng: {$_SESSION['username']}");
    }

    // Chuyển hướng sau khi bình luận để ngăn việc gửi form lặp lại
    header("Location: index.php");
    exit();
}

// Xóa bình luận
if (isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $comment_id, $_SESSION['username']);
    $stmt->execute();
    logAction("Xóa bình luận ID: $comment_id bởi người dùng: {$_SESSION['username']}");

    // Chuyển hướng để làm mới trang
    header("Location: index.php");
    exit();
}
// Lấy danh sách bài viết với phân trang
$posts_per_section = 8;
$total_posts = $conn->query("SELECT COUNT(*) AS count FROM posts")->fetch_assoc()['count'];
$total_sections = ceil($total_posts / $posts_per_section);

// Lấy section hiện tại từ URL
$current_section = isset($_GET['section']) ? (int)$_GET['section'] : 1;
$current_section = max(1, min($current_section, $total_sections)); // Giới hạn trong khoảng

// Tính toán vị trí bắt đầu
$start_index = ($current_section - 1) * $posts_per_section;

// Lấy danh sách bài viết cho section hiện tại
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT $start_index, $posts_per_section");

// Đăng xuất
if (isset($_GET['logout'])) {
    // Xóa cookie
    setcookie("username", "", time() - 3600, "/");
    session_unset();
    session_destroy();
    logAction("Đăng xuất: {$_SESSION['username']}");
    header("Location: index.php");
    exit();
}
?>