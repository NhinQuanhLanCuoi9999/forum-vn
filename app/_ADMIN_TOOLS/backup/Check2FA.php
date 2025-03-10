<?php
// Kiểm tra xem người dùng đã đăng nhập chưa
if (isset($_SESSION['username'])) {
    // Lấy giá trị username từ session
    $username = $_SESSION['username'];

    // Truy vấn vào bảng users để lấy giá trị 2fa và is_active
    $sql = "SELECT 2fa, is_active FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $twofa = $row['2fa'];
        $is_active = $row['is_active'];

        // Kiểm tra nếu is_active = 0, thì cập nhật 2fa thành 0
        if ($is_active == 0) {
            $update_sql = "UPDATE users SET 2fa = 0 WHERE username = '$username'";
            $conn->query($update_sql);
            $twofa = 0;  // Cập nhật biến $twofa để tránh chuyển hướng không cần thiết
        }

        // Kiểm tra xem 2fa có bằng 1 không và session 2fa chưa được thiết lập
        if ($twofa == 1 && (!isset($_SESSION['2fa']) || $_SESSION['2fa'] != 1)) {
            header("Location: ../src/2fa.php");
            exit();
        }
    }
}
?>
