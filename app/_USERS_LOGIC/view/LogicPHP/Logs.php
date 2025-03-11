<?php
// Hàm ghi log vào file
function logEdit($postId, $commentId, $newContent) {
    $logFile = '../logs/edit.txt';
    $date = date('d/m/Y | H:i:s');
    $username = $_SESSION['username'];
    $logMessage = "[$date] [$username] đã cập nhật bình luận ID=$commentId trong post ID=$postId với nội dung: $newContent\n";

    // Kiểm tra và ghi log vào file
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        $_SESSION['error'] = "Không thể ghi log vào file!";
    }
}
?>