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
    // Kiểm tra cả địa chỉ IPv4 và IPv6
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
// Hàm định dạng nội dung
function formatText($text) {
    // Định dạng chữ in đậm
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text); // **text**
    
    // Định dạng chữ lớn (in đậm) sử dụng ##text##
    $text = preg_replace('/##(.*?)##/', '<strong style="font-size: 1.5em;">$1</strong>', $text); // ##text##
    
    // Định dạng chữ gạch ngang
    $text = preg_replace('/--(.*?)--/', '<del>$1</del>', $text); // --text--
    
    // Định dạng chữ bị che (||text||)
    $text = preg_replace('/\|\|(.*?)\|\|/', '<span style="background-color: gray; color: transparent; cursor: pointer;" onclick="this.style.color=\'black\'; this.style.background=\'transparent\'">$1</span>', $text); // ||text||

    // Trả về kết quả đã định dạng
    return $text;
}
// Truy vấn để tìm các post_id không còn tồn tại trong bảng posts
$sql = "SELECT DISTINCT post_id FROM comments WHERE post_id NOT IN (SELECT id FROM posts)";
$result = $conn->query($sql);

// Nếu có các post_id không hợp lệ, xóa các comment tương ứng
if ($result->num_rows > 0) {
    // Tạo một chuỗi các id post không hợp lệ
    $invalidPostIds = [];
    while ($row = $result->fetch_assoc()) {
        $invalidPostIds[] = $row['post_id'];
    }

    // Chuyển đổi mảng thành chuỗi để sử dụng trong câu lệnh SQL
    $invalidPostIdsString = implode(',', $invalidPostIds);

    // Xóa tất cả các comment có post_id không hợp lệ
    $deleteSql = "DELETE FROM comments WHERE post_id IN ($invalidPostIdsString)";
    $conn->query($deleteSql);
}
// Đăng ký
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra nếu hai mật khẩu không trùng khớp
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Hai mật khẩu không trùng khớp!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $checkUser = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $checkUser->bind_param("s", $username);
        $checkUser->execute();
        $result = $checkUser->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Tài khoản đã tồn tại!";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);
            $stmt->execute();
            $_SESSION['success'] = "Đăng ký thành công!";
            logAction("Đăng ký tài khoản: $username");
        }
    }

    // Chỉ chuyển hướng nếu không có lỗi
    if (!isset($_SESSION['error']) && !isset($_SESSION['success'])) {
        header("Location: index.php");
        exit();
    }
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
            $error = "Mật khẩu không chính xác!";
        }
    } else {
        $error = "Tài khoản không tồn tại!";
    }
}

// Đăng bài
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post'])) {
    $content = $_POST['content'];
    $description = $_POST['description'];

    // Kiểm tra có emoji trong nội dung và mô tả
    if (preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $content) || 
        preg_match('/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $description)) {
        $_SESSION['error'] = "Nội dung và mô tả không được chứa emoji.";
    } else {
        // Xử lý tệp tin tải lên
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Các loại tệp được phép
        $allowedExt = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];

        // Biến lưu tên tệp mới
        $newFileName = null;

        // Kiểm tra nếu có tệp tin được tải lên
        if ($fileName) {
            // Kiểm tra loại tệp và kích thước tệp (tối đa 5MB)
            if (in_array($fileExt, $allowedExt) && $fileSize <= 5 * 1024 * 1024 && $fileError === 0) {
                // Kiểm tra và tạo thư mục uploads nếu chưa tồn tại
                if (!file_exists('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                // Tạo tên tệp mới để tránh trùng lặp
                $newFileName = uniqid('', true) . '.' . $fileExt;
                $fileDestination = 'uploads/' . $newFileName;

                // Di chuyển tệp đến thư mục uploads
                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Lưu bài viết vào cơ sở dữ liệu
                    $stmt = $conn->prepare("INSERT INTO posts (content, description, file, username) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $content, $description, $newFileName, $_SESSION['username']);
                    $stmt->execute();
                    logAction("Đăng bài mới của người dùng: {$_SESSION['username']}");
                    header("Location: index.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Đã xảy ra lỗi khi tải tệp lên.";
                }
            } else {
                $_SESSION['error'] = "Bạn chỉ có thể đăng tệp Word, Excel, PowerPoint, TxT dưới 5 MB.";
            }
        } else {
            // Nếu không có tệp tin thì chỉ lưu nội dung bài viết
            $stmt = $conn->prepare("INSERT INTO posts (content, description, username) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $content, $description, $_SESSION['username']);
            $stmt->execute();
            logAction("Đăng bài mới của người dùng: {$_SESSION['username']}");
            header("Location: index.php");
            exit();
        }
    }

    // Hiển thị thông báo lỗi nếu có
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
    }
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
        $error = "Bạn không có quyền xóa bài viết này!";
    }

    // Chuyển hướng để làm mới trang
    header("Location: index.php");
    exit();
}

// Thêm bình luận
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    // Định dạng nội dung bình luận
    $formatted_content = formatText($content);

    $stmt = $conn->prepare("INSERT INTO comments (post_id, content, username) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $post_id, $formatted_content, $_SESSION['username']);
    $stmt->execute();
    logAction("Thêm bình luận vào bài viết ID: $post_id bởi người dùng: {$_SESSION['username']}");

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

// Lấy danh sách bài viết
$posts = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");

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
    <title>Forum</title>
    <link rel="icon" href="favicon.png" type="image/png">
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(90deg, azure, lightblue); /* Hiệu ứng gradient từ màu azure sang màu lightblue */
    margin: 0;
    padding: 0;
}

        h1, h2 {
            color: #333;
        }

        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }

        form {
            background: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="password"],
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #4cae4c;
        }

        .error {
            color: red;
            margin: 10px 0;
        }

        .success {
            color: green;
            margin: 10px 0;
        }
.post {
    background: linear-gradient(to bottom right, #cce5ff, #99ccff); /* Gradient từ màu xanh nhạt đến màu xanh đậm hơn */
    padding: 10px;
    margin: 10px 0;
    border-radius: 5px;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.post h3 {
    word-wrap: break-word; /* Ngắt dòng nếu từ quá dài */
    overflow-wrap: break-word; /* Ngắt dòng từ */
    white-space: normal; /* Đảm bảo không giữ nguyên tất cả trong một dòng */
}

        .comment {
    background: linear-gradient(to bottom right, #fff2cc, #ffe6b3); /* Gradient từ màu vàng nhạt đến vàng nhạt hơn */
    padding: 5px;
    margin: 5px 0;
    border-radius: 3px;
}

        #register-form, #login-form {
            display: none;
        }

        .toggle-link {
            color: blue;
            cursor: pointer;
            text-decoration: underline;
        }

        .no-posts {
            font-style: italic;
            color: #666;
        }
        .alert {
    padding: 5px; /* Giảm padding để nhỏ lại khung */
    margin-bottom: 15px;
    border: 1px solid transparent;
    border-radius: 4px;
     text-align: left; /* Căn chỉnh văn bản sang bên trái */
}

.alert-danger {
    color: #a94442;
    background-color: #f2dede;
    border-color: #ebccd1;
    padding: 6px 15px; /* Giảm padding riêng cho khung đỏ */
    width: auto; /* Đặt width là auto để khung tự điều chỉnh kích thước */
    display: inline-block; /* Để khung chỉ rộng bằng nội dung bên trong */
    transform: translate(-65px, 150px);
}
.delete-button {
    background-color: red; /* Màu nền đỏ */
    color: white; /* Màu chữ trắng */
    border: none; /* Không có viền */
    outline: none; /* Không có viền ngoài */
    padding: 10px 20px; /* Khoảng cách bên trong nút */
    font-size: 16px; /* Kích thước chữ */
    cursor: pointer; /* Hiển thị con trỏ chuột khi di chuột qua nút */
    border-radius: 5px; /* Bo góc cho nút */
    transition: background-color 0.3s; /* Hiệu ứng chuyển đổi màu nền */
}

.delete-button:hover {
    background-color: darkred; /* Màu nền khi di chuột qua nút */
}
        /* Style cho thông báo che phủ toàn bộ màn hình */
        #mobile-warning 
        {
            display: none; /* Bắt đầu với trạng thái ẩn */
            position: fixed; /* Đặt vị trí cố định */
            top: 0;
            left: 0;
            right: 0;
            bottom: 0; /* Phủ kín toàn bộ chiều cao */
            background: rgba(255, 0, 0, 1); /* Nền đỏ hoàn toàn không trong suốt */
            color: white; /* Màu chữ trắng */
            text-align: center; /* Canh giữa văn bản */
            padding-top: 20%; /* Đưa thông báo xuống giữa */
            font-size: 24px; /* Kích thước chữ */
            z-index: 1000; /* Đảm bảo thông báo ở trên cùng */
        }
  .admin-button {
            background-color: #FFD700;
            color: black; /* Màu chữ trắng */
            font-family: 'Poppins', sans-serif;
            padding: 10px 15px; /* Padding */
            text-align: center; /* Canh giữa */
            text-decoration: none; /* Không gạch chân */
            display: inline-block; /* Hiển thị inline-block */
            font-size: 16px; /* Kích thước chữ */
            margin: 10px 20px; /* Margin */
            cursor: pointer; /* Con trỏ chuột */
            border: none; /* Không viền */
            border-radius: 5px; /* Bo góc */
            transition: background-color 0.3s; /* Hiệu ứng chuyển màu */
             transform: translate(210px, 70px);
        }

        .admin-button:hover {
            background-color: #45a049; /* Màu khi hover */
        }
        /* Ngăn cuộn trang */
        body.no-scroll {
            overflow: hidden; /* Ngăn không cho cuộn */
        }
        .comments-container {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.5s ease-out;
}

.comments-container.open {
    max-height: 500px; /* Tùy chỉnh giới hạn chiều cao phù hợp */
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
            <p>Chưa có tài khoản? <span class="toggle-link" onclick="toggleForms()">Đăng ký</span></p>
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
    <p>Đã có tài khoản? <span class="toggle-link" onclick="toggleForms()">Đăng nhập</span></p>
</form>

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
        <form method="post" action="index.php" enctype="multipart/form-data">
            <h2>Đăng bài viết</h2>
            <?php if (isset($_SESSION['error'])): ?>
                <div style="color: red;"><?php echo $_SESSION['error']; ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            <textarea name="content" placeholder="Nội dung bài viết" required maxlength="200"></textarea>
            <input type="text" name="description" placeholder="Mô tả ngắn" required maxlength="500">
            <!-- Chỉ cho phép tải lên tệp tin văn phòng và tệp TXT -->
            <input type="file" name="file" accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
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
    </style>
</head>
<body>

<button id="optionsBtn">Tùy chọn</button>

<div id="optionsMenu" class="dropdown-content">
    <a href="change_password.php">Đổi mật khẩu</a>
    <a href="network-config.php">Cấu Hình IP</a>
    <a href="tos.html">Điều khoản dịch vụ</a>
    <a href="index.php?logout=true">Đăng xuất</a>
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