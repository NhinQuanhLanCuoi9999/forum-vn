<?php
// Hàm kiểm tra từ cấm
function containsBadWords($content) {
    // Đọc các từ cấm từ file badwords.txt
    $badWords = file('badwords.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($badWords as $word) {
        // Tạo pattern cho phép chỉ nhận diện từ cấm mà không bị dính với dấu cách hay dấu câu
        // Sử dụng \W để xác định ký tự không phải là chữ cái hoặc số
        $pattern = '/(?<![a-zA-Z0-9])' . preg_quote($word, '/') . '(?![a-zAZ0-9])/iu';
        if (preg_match($pattern, $content)) {
            return true;
        }
    }
    return false;
}
?>