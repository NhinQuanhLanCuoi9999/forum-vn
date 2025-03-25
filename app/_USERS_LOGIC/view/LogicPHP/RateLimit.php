<?php
function checkRateLimit($postId) {
    // Bắt đầu session nếu chưa có
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Kiểm tra nếu block_time không tồn tại hoặc đã qua 5 phút thì reset count
    if (!isset($_SESSION['block_time']) || (time() - $_SESSION['block_time'] >= 5 * 60)) {
        $_SESSION['count'] = 0;
        $_SESSION['block_time'] = time(); // Cập nhật lại thời gian block
    }

    // Khởi tạo count nếu chưa có
    if (!isset($_SESSION['count'])) {
        $_SESSION['count'] = 0;
    }

    // Nếu đã vượt quá giới hạn thao tác
    if ($_SESSION['count'] >= 5) {
        $remaining = max(0, (5 * 60) - (time() - $_SESSION['block_time']));
        $minutes = floor($remaining / 60);
        $seconds = $remaining % 60;

        echo "<script>
                alert('Bạn đã vượt quá số lần thao tác. Vui lòng thử lại sau {$minutes} phút {$seconds} giây.');
                window.location.href='view.php?id=$postId';
              </script>";
        exit();
    }

    // Nếu chưa bị chặn, tăng số lần thao tác
    $_SESSION['count']++;
}

?>
