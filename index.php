<?php
session_start();
include('config.php');

// Kiểm tra nếu người dùng đã xác thực captcha bằng cookie
if (!isset($_COOKIE['captcha_verified'])) {
    header("Location: captcha_verification.php"); // Chuyển hướng đến trang xác thực captcha
    exit();
}

// Kiểm tra trạng thái cấm trước khi cho phép truy cập vào index.php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];

if ($username) {
    $stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
    $stmt->bind_param("ss", $username, $ip_address);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu bị cấm, chuyển hướng đến warning.php
        header("Location: warning.php");
        exit();
    }
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Forum</title>
    <link rel="icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="styles.css">
 <style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(90deg, lavender, lightcyan); /* Gradient từ lavender sang lightcyan */
        margin: 0;
        padding: 0;
    }

    h1, h2 {
        color: #333;
        text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3); /* Thêm bóng cho tiêu đề */
        text-transform: uppercase; /* Chuyển thành chữ in hoa */
    }

    .container {
        width: 95%;
        margin: auto;
        overflow: hidden;
        border: 2px solid #ddd; /* Thêm viền cho container */
        padding: 20px; /* Thêm padding */
        background: rgba(255, 255, 255, 0.9); /* Nền trắng mờ */
        border-radius: 15px; /* Bo góc */
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2); /* Đổ bóng */
    }

    form {
        background: #fff;
        padding: 30px;
        margin: 30px 0;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    input[type="text"],
    input[type="password"],
    textarea {
        width: 100%;
        padding: 15px;
        margin: 15px 0;
        border: 2px solid #ccc;
        border-radius: 10px;
        font-size: 16px;
        box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1); /* Đổ bóng vào bên trong */
    }

    button {
        background: #5cb85c;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 10px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s; /* Thêm hiệu ứng chuyển màu và phóng to */
    }

    button:hover {
        background: #4cae4c;
        transform: scale(1.05); /* Phóng to khi hover */
    }

    .error {
        color: red;
        font-weight: bold;
        margin: 15px 0;
        text-transform: uppercase; /* Chuyển thành chữ in hoa */
    }

    .success {
        color: green;
        margin: 15px 0;
        font-size: 18px;
        font-style: italic; /* In nghiêng */
    }

    .spoil {
        background-color: #333;
        color: #333;
        padding: 10px;
        cursor: pointer;
        border-radius: 10px;
        transition: color 0.5s ease, opacity 0.5s ease;
        opacity: 1;
        font-weight: bold; /* In đậm */
    }

    .spoil.open {
        color: #fff;
        opacity: 0.3;
    }

    .post {
        background: linear-gradient(to bottom right, #e0f7fa, #b2ebf2); /* Gradient từ màu xanh biển rất nhạt */
        padding: 15px;
        margin: 15px 0;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .comment {
        background: linear-gradient(to bottom right, #fffde7, #fff9c4); /* Gradient từ màu vàng nhạt */
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
    }


.admin-button {
    background-color: #FFD700;
    color: black; /* Màu chữ */
    font-family: 'Poppins', sans-serif;
    padding: 15px 25px; /* Padding lớn hơn */
    font-size: 18px; /* Kích thước chữ lớn hơn */
    margin: 10px; /* Margin */
    cursor: pointer; /* Con trỏ chuột */
    border: none; /* Không viền */
    border-radius: 20px; /* Bo góc nhiều hơn */
    transition: background-color 0.3s, transform 0.3s, box-shadow 0.3s; /* Thêm hiệu ứng chuyển đổi cho box-shadow và transform */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Hiệu ứng đổ bóng */
    display: flex; /* Sử dụng flexbox */
    justify-content: center;
    align-items: center;
}

.admin-button:hover {
    background-color: #45a049; /* Màu khi hover */
    transform: scale(1.1); /* Tăng kích thước khi hover */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3); /* Bóng đổ đậm hơn khi hover */
}

    .alert {
        padding: 10px;
        margin-bottom: 20px;
        border: 2px solid transparent;
        border-radius: 10px;
        background-color: rgba(255, 0, 0, 0.8); /* Nền đỏ mờ */
        color: white;
        font-size: 18px;
    }

    .delete-button {
        background-color: darkred;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 18px;
        cursor: pointer;
        border-radius: 10px;
        transition: background-color 0.3s, transform 0.3s;
    }

    .delete-button:hover {
        background-color: red;
        transform: scale(1.05);
    }

    .comments-container {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease-out;
    }

    .comments-container.open {
        max-height: 500px;
    }
#mobile-warning {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: white; /* Nền đỏ hoàn toàn */
  z-index: 3000;
  display: flex;
  justify-content: center;
  align-items: center;
}

#mobile-warning .content {
  position: relative;
  background-color: transparent;
  color: white; /* Màu chữ */
  z-index: 3001; /* Đảm bảo chữ hiển thị trên nền */
}
</style>
   <script>
        function toggleForms() {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            
            // Toggle the display of the forms
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }
    </script>
  <script>
    let isFormFocused = false;
let isFormFilled = false;
let isRefreshing = true;

function toggleForms() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    
    // Toggle the display of the forms
    if (loginForm.style.display === 'none') {
        loginForm.style.display = 'block';
        registerForm.style.display = 'none';
    } else {
        loginForm.style.display = 'none';
        registerForm.style.display = 'block';
    }
}

// Không làm mới trang khi quét khối
document.addEventListener('selectionchange', () => {
    if (document.getSelection().toString()) {
        isRefreshing = false; // Ngừng refresh khi có lựa chọn
    } else {
        isRefreshing = true; // Bắt đầu refresh lại khi không có lựa chọn
    }
});

// Biến http:// hoặc https:// thành liên kết
document.addEventListener('DOMContentLoaded', () => {
    const posts = document.querySelectorAll('.post');
    posts.forEach(post => {
        const content = post.querySelector('h3');
        content.innerHTML = convertLinks(content.innerHTML);
    });
});

function convertLinks(text) {
    return text.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank">$1</a>');
}

// Kiểm tra khi form đang được focus (đang có con trỏ trong form)
document.addEventListener('focusin', function (event) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
        isFormFocused = true;
    }
});

// Kiểm tra khi form không còn được focus
document.addEventListener('focusout', function (event) {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') {
        isFormFocused = false;
    }
});

// Kiểm tra nếu form đã có ký tự
document.addEventListener('input', function (event) {
    const inputs = document.querySelectorAll('input[type="text"], textarea');
    isFormFilled = Array.from(inputs).some(input => input.value.trim() !== '');
});

// Refresh trang mỗi 10 giây, trừ khi đang nhập liệu
setInterval(function () {
    if (isRefreshing && !isFormFocused && !isFormFilled) {
        location.reload();
    }
}, 10000); // 10000 milliseconds = 10 seconds
</script>
<script>
// Hàm để mở/đóng spoil block
function toggleSpoiler(element, id) {
    let isRevealed = localStorage.getItem(id) === 'true';
    if (isRevealed) {
        // Đóng spoil block
        element.style.color = '#333';
        localStorage.setItem(id, 'false');
    } else {
        // Mở spoil block
        element.style.color = '#fff';
        localStorage.setItem(id, 'true');
    }
}

// Khi trang tải lại, kiểm tra trạng thái của spoil block từ localStorage
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.spoil').forEach(function (element) {
        let id = element.id;
        let isRevealed = localStorage.getItem(id) === 'true';
        if (isRevealed) {
            element.style.color = '#fff'; // Mở spoil block
        }
    });
});
</script>
</head>
<body>
   <div id="mobile-warning">
        Vui lòng bật chế độ xem trên máy tính
    </div>

    <script>
        // Kiểm tra kích thước màn hình
        function checkScreenSize() {
            const warning = document.getElementById('mobile-warning');
            if (window.innerWidth < 768) { // Nếu màn hình nhỏ hơn 768px
                warning.style.display = 'flex'; // Hiện thông báo
                document.body.classList.add('no-scroll'); // Ngăn cuộn trang
            } else {
                warning.style.display = 'none'; // Ẩn thông báo
                document.body.classList.remove('no-scroll'); // Cho phép cuộn trang
            }
        }

        // Gọi hàm khi trang được tải, khi kích thước màn hình thay đổi,
        // và khi thay đổi hướng màn hình
        window.onload = checkScreenSize;
        window.onresize = checkScreenSize;
        window.orientationchange = checkScreenSize; // Kiểm tra hướng màn hình
    </script>
<div class="container">
    <h1>Forum</h1>
    <?php if (!isset($_SESSION['username'])): ?>
        <!-- Hiển thị form nếu chưa đăng nhập -->
        <form id="login-form" method="post" action="index.php" style="display: block;">
            <h2>Đăng nhập</h2>
            <input type="text" name="username" placeholder="Tên đăng nhập" required maxlength="50">
            <input type="password" name="password" placeholder="Mật khẩu" required>
            <button type="submit" name="login">Đăng nhập</button>
            <p>Chưa có tài khoản? <span class="toggle-link" style="color: red;"  onclick="toggleForms()">Đăng ký</span></p>
        </form>
        <form id="register-form" method="post" action="index.php" style="display: none;">
        <h2>Đăng ký</h2>
<form id="registrationForm">
    <input type="text" name="username" placeholder="Tên đăng nhập" required pattern="^[a-zA-Z0-9]{5,30}$"
        title="Vui lòng chỉ nhập ký tự chữ và số không dấu và không có khoảng trắng hoặc ký tự đặc biệt. Nhập từ 5 đến 30 ký tự.">
    <input type="password" name="password" placeholder="Mật khẩu" required 
        minlength="6" maxlength="30" 
        pattern="^[a-zA-Z0-9]{6,30}$"
        title="Vui lòng chỉ nhập ký tự chữ và số, không có khoảng trắng hoặc ký tự đặc biệt. Nhập từ 6 đến 30 ký tự.">
    <input type="password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
    
    <!-- Checkbox và liên kết -->
    <label>
        <input type="checkbox" id="agreeCheckbox"> 
        Bằng cách nhấn vào nút này, bạn đồng ý <a href="tos.html" target="_blank"><strong>Điều khoản dịch vụ</strong><b>.</b></a> <br>
    </label>
    
    <!-- Nút đăng ký mặc định xám và có hiệu ứng chuyển màu -->
    <button type="submit" name="register" id="registerBtn" disabled style="background-color: #9e9e9e;">Đăng ký</button>
 <p>Đã có tài khoản? <span class="toggle-link" style="color: red;" onclick="toggleForms()">Đăng nhập</span></p></form>

<style>
    /* Thêm hiệu ứng chuyển màu */
    #registerBtn {
        transition: background-color 0.3s ease, opacity 0.3s ease;
        opacity: 0.7; /* Mờ dần khi không thể bấm */
    }
    
    #registerBtn:enabled {
        opacity: 1; /* Đậm lên khi bật */
    }
</style>

<script>
    const agreeCheckbox = document.getElementById('agreeCheckbox');
    const registerBtn = document.getElementById('registerBtn');
    const registrationForm = document.getElementById('registrationForm');

    // Xử lý checkbox và nút đăng ký
    agreeCheckbox.addEventListener('change', function() {
        if (this.checked) {
            registerBtn.style.backgroundColor = '#4CAF50';  // Màu khi checkbox được chọn
            registerBtn.disabled = false;
        } else {
            registerBtn.style.backgroundColor = '#9e9e9e';  // Màu khi checkbox chưa chọn
            registerBtn.disabled = true;
        }
    });

    // Kiểm tra trước khi submit
    registrationForm.addEventListener('submit', function(event) {
        if (!agreeCheckbox.checked) {
            event.preventDefault();  // Ngừng submit form
            alert("Bạn chưa tick vào checkbox.");
        }
    });
</script>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?php echo $_SESSION['success']; ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
        </form>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php endif; ?>
    <?php else: ?>
        <!-- Hiển thị form đăng bài nếu đã đăng nhập -->
   <form action="index.php" method="POST" enctype="multipart/form-data">
    <h2>Đăng bài viết</h2>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <textarea name="content" placeholder="Nội dung bài viết" required maxlength="200"></textarea>
    <input type="text" name="description" placeholder="Mô tả ngắn" required maxlength="500">
    
    <button type="submit" name="post">Đăng bài</button>
</form>
   <style>
        /* Định dạng cho menu */
        #optionsMenu {
            display: block;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
            opacity: 0;
            pointer-events: none; /* Vô hiệu hóa sự kiện nhấp chuột khi menu ẩn */
            transition: opacity 0.5s ease, max-height 0.5s ease;
            max-height: 0;
        }

        /* Định dạng cho từng tùy chọn */
        #optionsMenu a {
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        #optionsMenu a:hover {
            background-color: #f1f1f1;
        }

        /* Định dạng cho nút */
        #optionsBtn {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }

        #optionsBtn:hover {
            background-color: #3e8e41;
        }
        .pagination {
    display: flex;
    justify-content: center; /* Căn giữa các nút phân trang */
    align-items: center; /* Căn giữa theo chiều dọc */
    margin: 20px 0; /* Khoảng cách trên và dưới */
}

.pagination a {
    text-decoration: none; /* Bỏ gạch chân */
    color: #007bff; /* Màu chữ cho các liên kết */
    padding: 10px 15px; /* Khoảng cách cho các nút */
    margin: 0 5px; /* Khoảng cách giữa các nút */
    border: 1px solid #007bff; /* Đường viền cho các nút */
    border-radius: 5px; /* Bo tròn góc */
    transition: background-color 0.3s, color 0.3s; /* Hiệu ứng chuyển tiếp */
}

.pagination a:hover {
    background-color: #007bff; /* Màu nền khi hover */
    color: white; /* Màu chữ khi hover */
}

.pagination strong {
    background-color: #007bff; /* Màu nền cho section hiện tại */
    color: white; /* Màu chữ cho section hiện tại */
    padding: 10px 15px; /* Khoảng cách cho nút hiện tại */
    border-radius: 5px; /* Bo tròn góc */
}
    </style>
</head>
<body>

<button id="optionsBtn">Tùy chọn</button>

<div id="optionsMenu" class="dropdown-content">
    <a href="info_user.php"><i class="fas fa-user"></i> Thông Tin</a>
    <a href="network-config.php"><i class="fas fa-network-wired"></i> Cấu Hình IP</a>
    <a href="tos.html"><i class="fas fa-file-contract"></i> Điều khoản dịch vụ</a>
    <a href="index.php?logout=true"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
</div>
<script>
    var menu = document.getElementById("optionsMenu");

    document.getElementById("optionsBtn").addEventListener("click", function() {
        if (menu.style.opacity === "1") {
            // Khi menu đang mở, đóng lại
            menu.style.opacity = "0";
            menu.style.maxHeight = "0";
            menu.style.pointerEvents = "none";  // Vô hiệu hóa nhấp chuột khi menu đóng
        } else {
            // Khi menu đóng, mở lại
            menu.style.display = "block";  // Đảm bảo menu luôn hiển thị
            setTimeout(function() {
                menu.style.opacity = "1";
                menu.style.maxHeight = "200px"; // Tùy chỉnh chiều cao tối đa của menu
                menu.style.pointerEvents = "auto";  // Bật lại sự kiện nhấp chuột khi menu mở
            }, 10);
        }
    });

    // Khi nhấn ra ngoài menu sẽ đóng
    window.onclick = function(event) {
        if (!event.target.matches('#optionsBtn')) {
            if (menu.style.opacity === "1") {
                menu.style.opacity = '0';
                menu.style.maxHeight = '0';
                menu.style.pointerEvents = 'none';  // Vô hiệu hóa nhấp chuột khi menu đóng
                setTimeout(function() {
                    menu.style.display = 'none';
                }, 500); // Thời gian khớp với transition
            }
        }
    }
</script>
<?php
echo "<div class='pagination'>";

// Liên kết đến section đầu tiên
if ($current_section > 1) {
    echo "<a href='index.php?section=1'>&lt;&lt;</a> ";
}

// Liên kết đến section trước
if ($current_section > 1) {
    echo "<a href='index.php?section=" . ($current_section - 1) . "'>&lt;</a> ";
}

// Hiển thị các liên kết section gần với section hiện tại
$range = 7; // Số section hiển thị xung quanh section hiện tại
for ($i = max(1, $current_section - $range); $i <= min($total_sections, $current_section + $range); $i++) {
    if ($i == $current_section) {
        echo "<strong>$i</strong> "; // Đánh dấu section hiện tại
    } else {
        echo "<a href='index.php?section=$i'>$i</a> ";
    }
}

// Liên kết đến section tiếp theo
if ($current_section < $total_sections) {
    echo "<a href='index.php?section=" . ($current_section + 1) . "'>&gt;</a> ";
}

// Liên kết đến section cuối cùng
if ($current_section < $total_sections) {
    echo "<a href='index.php?section=$total_sections'>&gt;&gt;</a>";
}

echo "</div>";
 
?>
        <h2>Các bài viết</h2>
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($post = $posts->fetch_assoc()): ?>
      <div class="post">
    <h3><?php echo formatText($post['content']); ?></h3> <!-- Sử dụng formatText để định dạng nội dung -->
    <p><?php echo htmlspecialchars($post['description']); ?></p>
    <!-- Hiển thị liên kết tải xuống nếu có tệp tin -->
    <?php if ($post['file']): ?>
        <p>Tệp đính kèm: <a href="uploads/<?php echo htmlspecialchars($post['file']); ?>" download><?php echo htmlspecialchars($post['file']); ?></a></p>
    <?php endif; ?>
    <?php
        // Định dạng ngày tháng
        $createdAt = DateTime::createFromFormat('Y-m-d H:i:s', $post['created_at']);
        if ($createdAt) {
            $formattedDate = $createdAt->format('d/n/Y | H:i:s');
        } else {
            $formattedDate = 'Ngày không hợp lệ'; // hoặc một giá trị mặc định khác
        }
    ?>
    <small>Đăng bởi: <?php echo htmlspecialchars($post['username']); ?> vào <?php echo $formattedDate; ?></small>
    <?php if ($post['username'] == $_SESSION['username']): ?>
        <form method="get" action="index.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
            <input type="hidden" name="delete" value="<?php echo $post['id']; ?>">
            <button type="submit" class="delete-button">Xóa bài viết</button>
        </form>
    <?php endif; ?>
    
    <!-- Nút hiện/ẩn bình luận -->
    <button class="toggle-comments" data-post-id="<?php echo $post['id']; ?>">Hiện bình luận</button>
    <div class="comments" id="comments-<?php echo $post['id']; ?>" style="display: none;">
        <h4>Bình luận:</h4>
        <form method="post" action="index.php" class="comment-form">
            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
            <textarea name="content" placeholder="Nhập bình luận" required></textarea>
            <button type="submit" name="comment">Gửi bình luận</button>
        </form>
        <?php
        $post_id = $post['id'];
        $comments = $conn->query("SELECT * FROM comments WHERE post_id = $post_id ORDER BY created_at DESC");
        if ($comments->num_rows > 0):
            while ($comment = $comments->fetch_assoc()): ?>
                <div class="comment">
                    <strong><?php echo htmlspecialchars($comment['username']); ?></strong>: 
                    <span><?php echo $comment['content']; ?></span> <!-- Xóa htmlspecialchars ở đây -->
                    <?php if ($comment['username'] == $_SESSION['username']): ?>
                        <a href="index.php?delete_comment=<?php echo $comment['id']; ?>">Xóa bình luận</a>
                    <?php endif; ?>
                </div>
        <?php endwhile; ?>
        <?php else: ?>
            <p class="no-posts">Chưa có bình luận nào.</p>
        <?php endif; ?>
    </div>
</div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-posts">Chưa có bài viết nào.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
<script>
function toggleComments(postId) {
    var commentsDiv = document.getElementById('comments-' + postId);
    var button = document.querySelector('.toggle-comments[data-post-id="' + postId + '"]');
    
    // Kiểm tra trạng thái hiện tại của phần bình luận
    if (commentsDiv.style.display === 'block') {
        let height = commentsDiv.scrollHeight;

        commentsDiv.animate([{ height: height + 'px' }, { height: '0' }], {
            duration: 100,
            fill: 'forwards'
        }).onfinish = function () {
            commentsDiv.style.display = 'none';
            button.textContent = 'Hiện bình luận'; // Cập nhật nút sau khi ẩn
            localStorage.setItem('commentsVisible-' + postId, 'false'); // Cập nhật trạng thái
        };
    } else {
        commentsDiv.style.display = 'block'; // Hiện phần bình luận
        commentsDiv.style.height = '0'; // Đặt chiều cao ban đầu
        let height = commentsDiv.scrollHeight; // Lấy chiều cao thực

        commentsDiv.animate([{ height: '0' }, { height: height + 'px' }], {
            duration: 100,
            fill: 'forwards'
        }).onfinish = function () {
            button.textContent = 'Ẩn bình luận'; // Cập nhật nút sau khi hiện
            localStorage.setItem('commentsVisible-' + postId, 'true'); // Cập nhật trạng thái
        };
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const toggleButtons = document.querySelectorAll('.toggle-comments');

    toggleButtons.forEach(function (button) {
        const postId = button.getAttribute('data-post-id');
        const commentsSection = document.getElementById('comments-' + postId);

        // Kiểm tra trạng thái trong localStorage
        if (localStorage.getItem('commentsVisible-' + postId) === 'true') {
            commentsSection.style.display = 'block';
            commentsSection.style.height = commentsSection.scrollHeight + 'px'; // Đặt chiều cao cho bình luận
            button.textContent = 'Ẩn bình luận';
        } else {
            commentsSection.style.display = 'none';
            commentsSection.style.height = '0'; // Đặt chiều cao cho bình luận khi ẩn
            button.textContent = 'Hiện bình luận';
        }

        // Gắn sự kiện click
        button.addEventListener('click', function () {
            toggleComments(postId);
        });
    });
});
</script>
</body>
</html>