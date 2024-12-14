<?php
// Hàm kiểm tra từ cấm
function containsBadWords($content) {
    // Đọc các từ cấm từ file badwords.txt
    $badWords = file('../badwords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

// Hàm định dạng lại nội dung bình luận
function formatText($text) {
    // Có thể thêm các logic xử lý khác nếu cần
    return trim($text); // Giữ an toàn HTML
}

// Hàm ghi lại hành động (log)
function logAction($action) {
    // Ghi lại vào file log hoặc database nếu cần
    file_put_contents('../logs/log.txt', $action . PHP_EOL, FILE_APPEND);
}
// Kiểm tra trạng thái cấm trước khi cho phép truy cập vào index.php
$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Kiểm tra nếu username đã đăng nhập
if ($username) {
    // Chuẩn bị câu truy vấn kiểm tra xem có bản ghi nào trong bảng bans với username hoặc ip_address
    $stmt = $conn->prepare("SELECT * FROM bans WHERE username = ? OR ip_address = ?");
    $stmt->bind_param("ss", $username, $ip_address); // Liên kết biến với câu truy vấn
    $stmt->execute(); // Thực thi câu truy vấn
    $result = $stmt->get_result(); // Lấy kết quả từ câu truy vấn

    // Kiểm tra nếu có bản ghi nào (người dùng bị cấm)
    if ($result->num_rows > 0) {
        // Nếu bị cấm, chuyển hướng đến warning.php
        header("Location: ../src/warning.php");
        exit(); // Dừng mọi mã PHP còn lại
    }
}
?>