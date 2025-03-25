<?php
function checkRateLimit($postId) {
    // Khởi tạo block_time nếu chưa tồn tại
    if (!isset($_SESSION['block_time'])) {
        $_SESSION['block_time'] = time();
    }

    // Kiểm tra nếu đã qua 5 phút kể từ block_time -> Reset count & block_time
    if (time() - $_SESSION['block_time'] >= 5 * 60) {
        $_SESSION['count'] = 0;
        $_SESSION['block_time'] = time(); // Reset lại thời gian bắt đầu
    }

    // Khởi tạo count nếu chưa tồn tại
    if (!isset($_SESSION['count'])) {
        $_SESSION['count'] = 0;
    }

    // Nếu đã bị chặn
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

    // Nếu chưa bị chặn, tăng số lần thao tác và cập nhật block_time
    $_SESSION['count']++;
    if ($_SESSION['count'] < 5) {
        $_SESSION['block_time'] = time();
    }
}

?>
