<?php

// Kiểm tra nếu biến ss1_refresh chưa được khởi tạo trong session
if (!isset($_SESSION['ss1_refresh'])) {
    $_SESSION['ss1_refresh'] = 1; // Khởi tạo ss1_refresh với giá trị 1
} else {
    $_SESSION['ss1_refresh']++; // Tăng giá trị của ss1_refresh lên 1
}

// Kiểm tra nếu ss1_refresh > 1, thì mới xóa ss1 và ss1_seen, ss1_refresh 
if ($_SESSION['ss1_refresh'] > 1) {
    unset($_SESSION['ss1']);
    unset($_SESSION['ss1_seen']);
    unset($_SESSION['ss1_refresh']);
}

// Kiểm tra nếu session ss1 tồn tại
if (isset($_SESSION['ss1'])) {
    // Kiểm tra nếu session không hiển thị ss1
    if (!isset($_SESSION['ss1_seen'])) {
        // Nếu chưa được xem, hiển thị giá trị của ss1
        echo $_SESSION['ss1'];

        // Tạo session đánh dấu là đã xem ss1
        $_SESSION['ss1_seen'] = true;
    }
} else {
    // Nếu ss1 không tồn tại, reset session đã xem ss1
    unset($_SESSION['ss1_seen']);
}
?>